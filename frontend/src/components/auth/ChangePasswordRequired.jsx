// src/components/auth/ChangePasswordRequired.jsx
//
// "Require password change at next sign-in" (set by an administrator on the
// user): until a new password is saved, this is the only screen. The API
// refuses everything else (403 password_change_required).

import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Alert, App, Button, Form, Input } from 'antd';
import { LockOutlined, LogoutOutlined } from '@ant-design/icons';
import authApi from '../../services/auth/authApi';
import useAuth from '../../hooks/useAuth';
import applyFormErrors from '../../utils/formErrors';
import handleApiError from '../../utils/handleApiError';
import { APP_NAME } from '../../utils/brand';
import './login.css';

const ChangePasswordRequired = () => {
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const { message } = App.useApp();
  const { user, setAuth, clearAuth } = useAuth();
  const [saving, setSaving] = useState(false);

  const save = async (values) => {
    setSaving(true);
    try {
      const { data } = await authApi.updateProfile({ name: user.name, ...values });
      setAuth(data.user, data.user_roles, data.user_permissions);
      message.success('Password changed. Welcome!');
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  const signOut = async () => {
    try { await authApi.logout(); } catch { /* signed out locally anyway */ }
    clearAuth();
    navigate('/login', { replace: true });
  };

  return (
    <div className="login-page">
      <main className="login-panel" style={{ flex: 1, alignItems: 'center' }}>
        <header className="login-panel__top" style={{ width: '100%' }}>
          <div className="login-panel__brand" style={{ display: 'flex' }}>
            <span className="app-logo">{APP_NAME.charAt(0).toUpperCase()}</span>
            <span>{APP_NAME}</span>
          </div>
          <Button type="text" icon={<LogoutOutlined />} onClick={signOut}>Sign out</Button>
        </header>
        <section className="login-panel__content">
          <div className="login-panel__eyebrow">Security</div>
          <h1 className="login-panel__welcome">Set a new password<span>.</span></h1>
          <p className="login-panel__tagline">Hi {user?.name}, your administrator asked you to choose a new password before continuing.</p>
          <Alert type="info" showIcon title="Use at least 8 characters you haven't used here before." className="login-panel__alert" />
          <Form form={form} layout="vertical" onFinish={save} requiredMark={false} disabled={saving} className="login-panel__form">
            <Form.Item name="current_password" label="Current password" rules={[{ required: true, message: 'Enter your current password.' }]}>
              <Input.Password size="large" prefix={<LockOutlined />} autoComplete="current-password" autoFocus />
            </Form.Item>
            <Form.Item name="password" label="New password" rules={[{ required: true, message: 'Enter a new password.' }, { min: 8, message: 'At least 8 characters.' }]}>
              <Input.Password size="large" prefix={<LockOutlined />} autoComplete="new-password" />
            </Form.Item>
            <Form.Item name="password_confirmation" label="Confirm new password" dependencies={['password']}
              rules={[{ required: true, message: 'Confirm the new password.' }, ({ getFieldValue }) => ({
                validator: (_, value) => (!value || value === getFieldValue('password') ? Promise.resolve() : Promise.reject(new Error('The passwords don’t match.'))),
              })]}>
              <Input.Password size="large" prefix={<LockOutlined />} autoComplete="new-password" />
            </Form.Item>
            <Button type="primary" htmlType="submit" size="large" block loading={saving} className="login-panel__submit">Save and continue</Button>
          </Form>
        </section>
      </main>
    </div>
  );
};

export default ChangePasswordRequired;
