// src/pages/auth/ForgotPasswordPage.jsx — ask for a reset link by email.

import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Alert, Button, Form, Input, Result } from 'antd';
import { ArrowLeftOutlined, MailOutlined } from '@ant-design/icons';
import AuthShell from '../../components/auth/AuthShell';
import authApi from '../../services/auth/authApi';

const ForgotPasswordPage = () => {
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(null);   // the email the link went to
  const [error, setError] = useState(null);

  const send = async ({ email }) => {
    setLoading(true);
    setError(null);
    try {
      await authApi.forgotPassword({ email });
      setSent(email);
    } catch (err) {
      const status = err?.response?.status;
      setError(status === 429 ? 'Too many requests. Please wait a minute and try again.'
        : status === 422 ? 'Enter a valid email.' : 'We couldn’t send the link right now. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthShell eyebrow="Account recovery" title="Forgot your password"
      tagline={sent ? null : 'Enter your email and we’ll send you a link to choose a new password.'}>
      {sent ? (
        <Result status="success" style={{ padding: '8px 0' }} title="Check your email"
          subTitle={`If an account exists for ${sent}, a reset link is on its way. It expires in 60 minutes.`}
          extra={<Link to="/login"><Button type="primary" icon={<ArrowLeftOutlined />}>Back to sign in</Button></Link>} />
      ) : (
        <>
          {error && <Alert type="error" showIcon title={error} className="login-panel__alert" />}
          <Form layout="vertical" onFinish={send} requiredMark={false} disabled={loading} className="login-panel__form">
            <Form.Item label="Email" name="email" rules={[{ required: true, message: 'Enter your email.' }, { type: 'email', message: 'Enter a valid email.' }]}>
              <Input size="large" prefix={<MailOutlined />} placeholder="name@company.com" autoComplete="username" autoFocus />
            </Form.Item>
            <Button type="primary" htmlType="submit" size="large" block loading={loading} className="login-panel__submit">Send reset link</Button>
          </Form>
          <div className="login-panel__links"><Link to="/login"><ArrowLeftOutlined /> Back to sign in</Link></div>
        </>
      )}
    </AuthShell>
  );
};

export default ForgotPasswordPage;
