// src/components/auth/LoginForm.jsx
//
// The login page: the animated showcase on the left (wide screens) and the
// sign-in panel on the right (brand on top, form centred, footer at the
// bottom). Says why a previous session ended, warns about Caps Lock, maps
// each API status to a clear message, and follows a sign-in made in another
// tab (one session per browser).

import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Alert, App, Button, Checkbox, Form, Input } from 'antd';
import { ArrowRightOutlined, LockOutlined, MailOutlined, WarningFilled } from '@ant-design/icons';
import authApi from '../../services/auth/authApi';
import useAuth from '../../hooks/useAuth';
import { markActive, setSessionSettings, takeEndReason } from '../../utils/session';
import { getToken, TOKEN_KEY } from '../../utils/tokenHelper';
import { APP_NAME } from '../../utils/brand';
import AuthShell from './AuthShell';

const REMEMBER_KEY = 'remembered_email';
const rememberedEmail = () => {
  try { return localStorage.getItem(REMEMBER_KEY) || ''; } catch { return ''; }
};

const LoginForm = () => {
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const { message } = App.useApp();
  const { setAuth, setToken, setLoaded } = useAuth();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  // why the last session ended (set by the axios interceptor before its redirect)
  const [endReason] = useState(takeEndReason);
  const [capsLock, setCapsLock] = useState(false);

  // one session per browser: if another tab signs in meanwhile, go in with it
  useEffect(() => {
    const enter = () => {
      message.info('You are already signed in.');
      navigate('/', { replace: true });
    };
    const onStorage = (e) => { if (e.key === TOKEN_KEY && e.newValue) enter(); };
    const onVisible = () => { if (document.visibilityState === 'visible' && getToken()) enter(); };
    window.addEventListener('storage', onStorage);
    document.addEventListener('visibilitychange', onVisible);
    return () => {
      window.removeEventListener('storage', onStorage);
      document.removeEventListener('visibilitychange', onVisible);
    };
  }, [navigate, message]);

  const login = async ({ email, password, remember }) => {
    setLoading(true);
    setError(null);
    try {
      const { data } = await authApi.login({ email, password });
      try { remember ? localStorage.setItem(REMEMBER_KEY, email) : localStorage.removeItem(REMEMBER_KEY); } catch { /* storage blocked */ }
      setToken(data.access_token);
      setAuth(data.user, data.user_roles, data.user_permissions);
      setSessionSettings(data.session);
      markActive();
      setLoaded();
      navigate('/', { replace: true });
    } catch (err) {
      const status = err?.response?.status;
      if (status === 401) setError('Invalid email or password.');
      else if (status === 403) setError(err.response.data?.message || 'Your account is inactive. Please contact the administrator.');
      else if (status === 429) setError('Too many attempts. Please wait a minute and try again.');
      else if (status === 422) setError('Enter your email and password.');
      else if (err?.response) setError('We couldn’t sign you in right now. Please try again in a moment.');
      else setError('The server can’t be reached — check your connection.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthShell eyebrow="Sign in" title="Welcome back" tagline={`Enter your credentials to continue to ${APP_NAME}.`}>
      {(error || endReason) && (
        <Alert type={error ? 'error' : 'info'} showIcon title={error || endReason} className="login-panel__alert" />
      )}

      <Form form={form} layout="vertical" onFinish={login} requiredMark={false} disabled={loading}
        initialValues={{ email: rememberedEmail(), remember: !!rememberedEmail() }} className="login-panel__form">
        <Form.Item label="Email" name="email"
          rules={[{ required: true, message: 'Enter your email.' }, { type: 'email', message: 'Enter a valid email.' }]}>
          <Input size="large" prefix={<MailOutlined />} placeholder="name@company.com" autoComplete="username" autoFocus={!rememberedEmail()} />
        </Form.Item>
        <Form.Item label={<span className="login-panel__label-row">Password<Link to="/forgot-password" tabIndex={-1}>Forgot password?</Link></span>}
          name="password" rules={[{ required: true, message: 'Enter your password.' }]}
          extra={capsLock && <span className="login-panel__caps"><WarningFilled /> Caps Lock is on</span>}>
          <Input.Password size="large" prefix={<LockOutlined />} placeholder="••••••••" autoComplete="current-password"
            autoFocus={!!rememberedEmail()}
            onKeyUp={(e) => setCapsLock(e.getModifierState?.('CapsLock') ?? false)} onBlur={() => setCapsLock(false)} />
        </Form.Item>
        <Form.Item name="remember" valuePropName="checked" style={{ marginBottom: 20 }}>
          <Checkbox>Remember my email</Checkbox>
        </Form.Item>
        <Button type="primary" htmlType="submit" size="large" block loading={loading} className="login-panel__submit">
          Sign in <ArrowRightOutlined />
        </Button>
      </Form>
    </AuthShell>
  );
};

export default LoginForm;
