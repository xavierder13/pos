// src/pages/administration/approval_flows/ApprovalFlowFormModal.jsx
//
// Add / edit a flow: name, record type, active, and its levels in order —
// each approved by one user or by anyone with a role (Form.List rows).
// A Form.List dialog: the dialog is its own component, mounted only while open,
// so each open gets a fresh form store and initialValues apply cleanly
// (useModalForm's preserve={false} would wipe a row's values whenever
// Form.List remounts it — see useModalForm).

import { useState } from 'react';
import { App, Button, Card, Flex, Form, Input, Modal, Select, Switch, Typography } from 'antd';
import { COLORS } from '../../../utils/theme';
import { MinusCircleOutlined, PlusOutlined } from '@ant-design/icons';
import approvalFlowApi from '../../../services/administration/approvalFlowApi';
import handleApiError from '../../../utils/handleApiError';
import applyFormErrors from '../../../utils/formErrors';

const FlowDialog = ({ record, options, onClose, onSaved }) => {
  const { message } = App.useApp();
  const [form] = Form.useForm();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;
  const values = () => (isEdit
    ? { name: record.name, record_type: record.record_type, active: record.active, levels: record.levels.map(({ approver_type, approver_id }) => ({ approver_type, approver_id })) }
    : { name: '', record_type: undefined, active: true, levels: [{ approver_type: 'role', approver_id: undefined }] });

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await approvalFlowApi.update(record.id, values) : await approvalFlowApi.create(values);
      message.success(data.message);
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  const approverOptions = (type) => (type === 'role'
    ? options.roles.map((r) => ({ value: r.id, label: r.name }))
    : options.users.map((u) => ({ value: u.id, label: `${u.name} (${u.email})` })));

  return (
    <Modal open title={isEdit ? 'Edit Approval Flow' : 'Add Approval Flow'} width={640} keyboard={false}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form form={form} layout="vertical" disabled={saving} initialValues={values()}>
        <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter a name.' }]}>
          <Input placeholder="e.g. Purchase approval" autoFocus />
        </Form.Item>
        <Flex gap={16}>
          <Form.Item name="record_type" label="Applies to" style={{ flex: 1 }} rules={[{ required: true, message: 'Choose the kind of record.' }]}>
            <Select options={options.record_types} placeholder="Kind of record" />
          </Form.Item>
          <Form.Item name="active" label="Active" valuePropName="checked" extra="One active flow per kind of record.">
            <Switch />
          </Form.Item>
        </Flex>

        <Typography.Text strong>Levels (in order)</Typography.Text>
        <Form.List name="levels" rules={[{ validator: (_, levels) => (levels?.length ? Promise.resolve() : Promise.reject(new Error('Add at least one level.'))) }]}>
          {(fields, { add, remove }, { errors }) => (
            <div style={{ marginTop: 8 }}>
              {fields.map((field, index) => (
                <Card key={field.key} size="small" style={{ marginBottom: 8 }}
                  title={`Level ${index + 1}`}
                  extra={fields.length > 1 && <MinusCircleOutlined onClick={() => remove(field.name)} style={{ color: COLORS.error }} aria-label="Remove level" />}>
                  <Flex gap={12}>
                    <Form.Item name={[field.name, 'approver_type']} label="Approved by" style={{ width: 160, marginBottom: 0 }}>
                      <Select options={[{ value: 'role', label: 'Anyone with role' }, { value: 'user', label: 'A specific user' }]}
                        onChange={() => form.setFieldValue(['levels', field.name, 'approver_id'], undefined)} />
                    </Form.Item>
                    <Form.Item noStyle shouldUpdate={(a, b) => a.levels?.[field.name]?.approver_type !== b.levels?.[field.name]?.approver_type}>
                      {() => (
                        <Form.Item name={[field.name, 'approver_id']} label={form.getFieldValue(['levels', field.name, 'approver_type']) === 'user' ? 'User' : 'Role'}
                          style={{ flex: 1, marginBottom: 0 }} rules={[{ required: true, message: 'Choose the approver.' }]}>
                          <Select showSearch optionFilterProp="label" placeholder="Choose…"
                            options={approverOptions(form.getFieldValue(['levels', field.name, 'approver_type']))} />
                        </Form.Item>
                      )}
                    </Form.Item>
                  </Flex>
                </Card>
              ))}
              <Button type="dashed" block icon={<PlusOutlined />} onClick={() => add({ approver_type: 'role' })} disabled={fields.length >= 10}>Add level</Button>
              <Form.ErrorList errors={errors} />
            </div>
          )}
        </Form.List>
      </Form>
    </Modal>
  );
};

// record: null = closed, {} = add, a flow = edit
const ApprovalFlowFormModal = ({ record, ...props }) => (record ? <FlowDialog key={record.id ?? 'new'} record={record} {...props} /> : null);

export default ApprovalFlowFormModal;
