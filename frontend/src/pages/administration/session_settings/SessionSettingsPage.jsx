// src/pages/administration/session_settings/SessionSettingsPage.jsx
//
// Administration → Session Settings: how long a sign-in may sit idle and how
// long it may last at most. Stored in the database — saved values apply to
// every signed-in user on their next request, with no code change.

import { useEffect, useState } from 'react';
import { Alert, App, Button, Card, Col, Form, InputNumber, Row, Skeleton, Space, Typography } from 'antd';
import { ClockCircleOutlined, FieldTimeOutlined, SaveOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import AuditHistory from '../../../components/AuditHistory';
import sessionSettingApi from '../../../services/administration/sessionSettingApi';
import handleApiError from '../../../utils/handleApiError';
import applyFormErrors from '../../../utils/formErrors';
import { durationText, setSessionSettings } from '../../../utils/session';

const PRESETS_IDLE = [15, 30, 60, 120];
const PRESETS_LIFETIME = [240, 480, 720, 1440];

const SessionSettingsPage = () => {
  const { message } = App.useApp();
  const [form] = Form.useForm();
  const [limits, setLimits] = useState(null);
  const [saving, setSaving] = useState(false);
  const idle = Form.useWatch('idle_timeout_minutes', form);
  const lifetime = Form.useWatch('session_lifetime_minutes', form);

  useEffect(() => {
    const load = async () => {
      try {
        const { data } = await sessionSettingApi.get();
        form.setFieldsValue(data.data);
        setLimits(data.limits);
      } catch (error) {
        handleApiError(error, message);
      }
    };
    load();
  }, [form, message]);

  const save = async (values) => {
    setSaving(true);
    try {
      const { data } = await sessionSettingApi.update(values);
      setSessionSettings(data.data);   // this tab's timer follows at once
      message.success(data.message);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  const presets = (values, field) => (
    <Space wrap size={6} style={{ marginTop: 8 }}>
      {values.map((m) => <Button key={m} size="small" onClick={() => form.setFieldValue(field, m)}>{durationText(m)}</Button>)}
    </Space>
  );

  return (
    <>
      <PageHeader title="Session Settings" subtitle="Applies to everyone at once — no code change or restart needed." />
      {!limits ? <Card><Skeleton active /></Card> : (
        <Form form={form} layout="vertical" onFinish={save} disabled={saving} requiredMark={false}>
          <Row gutter={[16, 16]}>
            <Col xs={24} lg={12}>
              <Card title={<span><ClockCircleOutlined style={{ color: 'var(--accent)', marginRight: 8 }} />Idle timeout</span>}>
                <Form.Item name="idle_timeout_minutes" label="Sign out after this long without activity"
                  rules={[{ required: true, message: 'Enter the idle time.' }]}
                  extra={idle ? `= ${durationText(idle)}. A warning appears one minute before.` : null}>
                  <InputNumber min={limits.idle_min} max={limits.idle_max} suffix="min" style={{ width: '100%' }} />
                </Form.Item>
                {presets(PRESETS_IDLE, 'idle_timeout_minutes')}
              </Card>
            </Col>
            <Col xs={24} lg={12}>
              <Card title={<span><FieldTimeOutlined style={{ color: 'var(--accent)', marginRight: 8 }} />Session duration</span>}>
                <Form.Item name="session_lifetime_minutes" label="Sign out this long after signing in, even when active"
                  dependencies={['idle_timeout_minutes']}
                  rules={[{ required: true, message: 'Enter the session duration.' }, ({ getFieldValue }) => ({
                    validator: (_, value) => (!value || value >= (getFieldValue('idle_timeout_minutes') || 0)
                      ? Promise.resolve() : Promise.reject(new Error('Must be at least the idle time.'))),
                  })]}
                  extra={lifetime ? `= ${durationText(lifetime)}.` : null}>
                  <InputNumber min={limits.lifetime_min} max={limits.lifetime_max} suffix="min" style={{ width: '100%' }} />
                </Form.Item>
                {presets(PRESETS_LIFETIME, 'session_lifetime_minutes')}
              </Card>
            </Col>
            <Col span={24}>
              <Alert type="info" showIcon title="How it works"
                description={<Typography.Text type="secondary">Every API call counts as activity; background checks (the notification bell) don't. When either limit is reached, the user is signed out and the login page says why.</Typography.Text>} />
            </Col>
            <Col span={24}>
              <Button type="primary" htmlType="submit" icon={<SaveOutlined />} loading={saving}>Save settings</Button>
            </Col>
            <Col span={24}>
              <AuditHistory record="Session Settings" recordId={1} title="Change history" />
            </Col>
          </Row>
        </Form>
      )}
    </>
  );
};

export default SessionSettingsPage;
