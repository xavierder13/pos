// src/pages/auth/ResetPasswordPage.jsx — /reset-password?token=…&email=… (the link in the reset email).

import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { Alert, Button, Form, Input } from 'antd';
import { LockOutlined } from '@ant-design/icons';
import AuthShell from '../../components/auth/AuthShell';
import authApi from '../../services/auth/authApi';
import applyFormErrors from '../../utils/formErrors';
import { rememberEndReason } from '../../utils/session';

const ResetPasswordPage = () => {
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const token = params.get('token');
  const email = params.get('email');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const reset = async (values) => {
    setLoading(true);
    setError(null);
    try {
      const { data } = await authApi.resetPassword({ token, email, ...values });
      rememberEndReason(data.message);   // shown on the login page
      navigate('/login', { replace: true });
    } catch (err) {
      if (!applyFormErrors(form, err)) setError(err?.response?.data?.message || 'We couldn’t reset your password. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (!token || !email) {
    return (
      <AuthShell eyebrow="Account recovery" title="Link not valid">
        <Alert type="error" showIcon title="This reset link is incomplete. Open the link from the email again, or ask for a new one." className="login-panel__alert" />
        <Link to="/forgot-password"><Button type="primary" block size="large">Ask for a new link</Button></Link>
      </AuthShell>
    );
  }

  return (
    <AuthShell eyebrow="Account recovery" title="Choose a new password" tagline={`For ${email}. You’ll be signed out everywhere else.`}>
      {error && (
        <Alert type="error" showIcon title={error} className="login-panel__alert"
          action={<Link to="/forgot-password">New link</Link>} />
      )}
      <Form form={form} layout="vertical" onFinish={reset} requiredMark={false} disabled={loading} className="login-panel__form">
        <Form.Item name="password" label="New password" rules={[{ required: true, message: 'Enter a new password.' }, { min: 8, message: 'At least 8 characters.' }]}>
          <Input.Password size="large" prefix={<LockOutlined />} autoComplete="new-password" autoFocus />
        </Form.Item>
        <Form.Item name="password_confirmation" label="Confirm new password" dependencies={['password']}
          rules={[{ required: true, message: 'Confirm the new password.' }, ({ getFieldValue }) => ({
            validator: (_, value) => (!value || value === getFieldValue('password') ? Promise.resolve() : Promise.reject(new Error('The passwords don’t match.'))),
          })]}>
          <Input.Password size="large" prefix={<LockOutlined />} autoComplete="new-password" />
        </Form.Item>
        <Button type="primary" htmlType="submit" size="large" block loading={loading} className="login-panel__submit">Reset password</Button>
      </Form>
      <div className="login-panel__links"><Link to="/login">Back to sign in</Link></div>
    </AuthShell>
  );
};

export default ResetPasswordPage;
