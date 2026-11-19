// src/components/SessionTimeout.jsx
//
// The idle timer (rendered once by MainLayout). Session Settings come from
// the API (utils/session.js), so changing the idle time needs no code change.
// - Real activity (mouse, keys, scroll) in any tab is shared via localStorage.
// - While the user is active, a heartbeat tells the API at most every minute
//   (a page where the user only types sends no other call), so the API's
//   idle clock never runs ahead of this one.
// - One minute before the idle time ends: a warning with a countdown —
//   "Stay signed in" or "Sign out".
// - At the idle time: this tab signs out (token revoked, /login says why).
//   The API enforces the same limit on its own (CheckSessionActivity).

import { useCallback, useEffect, useRef, useState } from 'react';
import { Button, Modal, Progress, Typography } from 'antd';
import { COLORS } from '../utils/theme';
import { ClockCircleOutlined } from '@ant-design/icons';
import authApi from '../services/auth/authApi';
import { ACTIVITY_EVENTS, ACTIVITY_KEY, durationText, getIdleTimeoutMs, getSessionSettings, lastActivity, markActive, rememberEndReason } from '../utils/session';
import { clearTokens } from '../utils/tokenHelper';

const WARN_MS = 60 * 1000;
const TICK_MS = 1000;
const WRITE_THROTTLE_MS = 5000;
const HEARTBEAT_MS = 60 * 1000;

const SessionTimeout = ({ onSignOut }) => {
  const [secondsLeft, setSecondsLeft] = useState(null);   // null = no warning
  const lastWrite = useRef(0);
  const lastPing = useRef(0);
  const ended = useRef(false);
  const warning = secondsLeft !== null;

  // real activity → shared timestamp (throttled); ignored while the warning is up
  useEffect(() => {
    const onActivity = () => {
      if (warning) return;
      const now = Date.now();
      if (now - lastWrite.current < WRITE_THROTTLE_MS) return;
      lastWrite.current = now;
      markActive();
    };
    ACTIVITY_EVENTS.forEach((e) => window.addEventListener(e, onActivity, { passive: true }));
    return () => ACTIVITY_EVENTS.forEach((e) => window.removeEventListener(e, onActivity));
  }, [warning]);

  // another tab was active → close this tab's warning
  useEffect(() => {
    const onStorage = (e) => { if (e.key === ACTIVITY_KEY) setSecondsLeft(null); };
    window.addEventListener('storage', onStorage);
    return () => window.removeEventListener('storage', onStorage);
  }, []);

  useEffect(() => {
    markActive();
    lastPing.current = Date.now();
    const id = setInterval(() => {
      const idleMs = getIdleTimeoutMs();
      const now = Date.now();
      const idleFor = now - lastActivity();

      // active since the last heartbeat: tell the API (at most every minute)
      if (lastActivity() > lastPing.current && now - lastPing.current >= HEARTBEAT_MS) {
        lastPing.current = now;
        authApi.ping().catch(() => {});
      }

      if (idleFor >= idleMs) {
        if (ended.current) return;
        ended.current = true;
        rememberEndReason(`You were signed out after ${durationText(getSessionSettings().idle_timeout_minutes)} of inactivity.`);
        authApi.logout().catch(() => {}).finally(() => {
          clearTokens();
          window.location.assign('/login');
        });
      } else if (idleFor >= idleMs - WARN_MS) {
        setSecondsLeft(Math.ceil((idleMs - idleFor) / 1000));
      } else {
        setSecondsLeft(null);
      }
    }, TICK_MS);
    return () => clearInterval(id);
  }, []);

  const stay = useCallback(() => {
    markActive();
    lastPing.current = Date.now();
    setSecondsLeft(null);
    authApi.ping().catch(() => {});
  }, []);

  return (
    <Modal open={warning} keyboard={false} closable={false} centered width={420}
      title={<span><ClockCircleOutlined style={{ color: 'var(--accent)', marginRight: 8 }} />Are you still there?</span>}
      footer={[
        <Button key="out" onClick={onSignOut}>Sign out</Button>,
        <Button key="stay" type="primary" onClick={stay}>Stay signed in</Button>,
      ]}>
      <div style={{ textAlign: 'center', padding: '8px 0' }}>
        <Progress type="circle" size={96} percent={Math.max(0, ((secondsLeft ?? 0) / 60) * 100)}
          format={() => `${secondsLeft ?? 0}s`} strokeColor={COLORS.accent} />
        <Typography.Paragraph type="secondary" style={{ marginTop: 16, marginBottom: 0 }}>
          You'll be signed out soon because of inactivity.
        </Typography.Paragraph>
      </div>
    </Modal>
  );
};

export default SessionTimeout;
