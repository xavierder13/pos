// src/utils/session.js
//
// The SPA side of Session Settings (Administration → Session Settings, stored
// in the database — no code change to adjust them). The API enforces them
// (CheckSessionActivity); login / init send them as `session`, kept in
// localStorage so every tab and a reload use the same values. The idle timer
// itself is components/SessionTimeout.jsx; the 401 handling is in
// api/axiosInstance.js.

const SETTINGS_KEY = 'session_settings';
const REASON_KEY = 'session_ended_reason';
const DEFAULT_IDLE_MINUTES = 30;

// shared by every tab: the last real activity (mouse / keys) in any of them
export const ACTIVITY_KEY = 'last_activity_at';
export const ACTIVITY_EVENTS = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'wheel', 'scroll'];

// header for check-only calls (idle confirmation, the bell's unread count):
// checked by the API but not counted as activity — axios config { headers: PASSIVE_HEADERS }
export const PASSIVE_HEADERS = { 'X-Session-Check': 'passive' };

// record real activity now (shared with the other tabs)
export const markActive = () => {
  try { localStorage.setItem(ACTIVITY_KEY, String(Date.now())); } catch { /* storage blocked */ }
};

export const lastActivity = () => {
  try { return Number(localStorage.getItem(ACTIVITY_KEY)) || Date.now(); } catch { return Date.now(); }
};

export const setSessionSettings = (session) => {
  if (!session) return;
  try { localStorage.setItem(SETTINGS_KEY, JSON.stringify(session)); } catch { /* storage blocked: defaults */ }
};

export const getSessionSettings = () => {
  try {
    const s = JSON.parse(localStorage.getItem(SETTINGS_KEY));
    return { idle_timeout_minutes: Number(s?.idle_timeout_minutes) || DEFAULT_IDLE_MINUTES, session_lifetime_minutes: Number(s?.session_lifetime_minutes) || 720 };
  } catch {
    return { idle_timeout_minutes: DEFAULT_IDLE_MINUTES, session_lifetime_minutes: 720 };
  }
};

export const getIdleTimeoutMs = () => getSessionSettings().idle_timeout_minutes * 60 * 1000;

// "45 minutes", "1 hour", "1 hour 30 minutes"
export const durationText = (minutes) => {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  const part = (n, unit) => (n ? `${n} ${unit}${n === 1 ? '' : 's'}` : '');
  return [part(h, 'hour'), part(m, 'minute')].filter(Boolean).join(' ') || '0 minutes';
};

// The API's 401 messages that end a session → what the login page says
const ENDED_MESSAGES = {
  'Your session has expired due to inactivity.': () => `You were signed out after ${durationText(getSessionSettings().idle_timeout_minutes)} of inactivity.`,
  'Your session has expired.': () => 'Your session reached its time limit. Please sign in again.',
};

export const sessionEndReason = (error) => {
  const make = ENDED_MESSAGES[error?.response?.data?.message];
  return make ? make() : 'Your session has ended. Please sign in again.';
};

export const rememberEndReason = (reason) => {
  try { sessionStorage.setItem(REASON_KEY, reason); } catch { /* storage blocked */ }
};

export const takeEndReason = () => {
  try {
    const reason = sessionStorage.getItem(REASON_KEY);
    sessionStorage.removeItem(REASON_KEY);
    return reason;
  } catch {
    return null;
  }
};
