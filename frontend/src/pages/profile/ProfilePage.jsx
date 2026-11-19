// src/pages/profile/ProfilePage.jsx

import { useState } from 'react';
import { App, Button, Card, Descriptions, Form, Input, Space, Tag } from 'antd';
import PageHeader from '../../components/PageHeader';
import authApi from '../../services/auth/authApi';
import useAuth from '../../hooks/useAuth';
import handleApiError from '../../utils/handleApiError';
import applyFormErrors from '../../utils/formErrors';

const ProfilePage = () => {
  const { message } = App.useApp();
  const { user, roles, setAuth } = useAuth();
  const [form] = Form.useForm();
  const [saving, setSaving] = useState(false);

  const save = async (values) => {
    setSaving(true);
    try {
      const { data } = await authApi.updateProfile(values);
      setAuth(data.user, data.user_roles, data.user_permissions);
      form.setFieldsValue({ current_password: '', password: '', password_confirmation: '' });
      message.success(data.message);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <>
      <PageHeader title="My Profile" />
      <Space orientation="vertical" size={16} style={{ width: '100%', maxWidth: 640 }}>
        <Card>
          <Descriptions column={1} items={[
            { key: 'email', label: 'Email', children: user?.email },
            { key: 'roles', label: 'Roles', children: roles.length ? roles.map((r) => <Tag key={r}>{r}</Tag>) : '—' },
          ]} />
        </Card>
        <Card title="Account">
          <Form form={form} layout="vertical" onFinish={save} initialValues={{ name: user?.name }} disabled={saving}>
            <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter your name.' }]}>
              <Input />
            </Form.Item>
            <Form.Item name="current_password" label="Current password" extra="Only to change your password.">
              <Input.Password autoComplete="current-password" />
            </Form.Item>
            <Form.Item name="password" label="New password" rules={[{ min: 8, message: 'At least 8 characters.' }]}>
              <Input.Password autoComplete="new-password" />
            </Form.Item>
            <Form.Item name="password_confirmation" label="Confirm new password" dependencies={['password']}
              rules={[({ getFieldValue }) => ({
                validator: (_, value) => (!getFieldValue('password') || value === getFieldValue('password')
                  ? Promise.resolve() : Promise.reject(new Error('The passwords don\'t match.'))),
              })]}>
              <Input.Password autoComplete="new-password" />
            </Form.Item>
            <Button type="primary" htmlType="submit" loading={saving}>Save</Button>
          </Form>
        </Card>
      </Space>
    </>
  );
};

export default ProfilePage;
