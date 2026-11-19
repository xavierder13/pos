// src/pages/administration/users/UserFormModal.jsx
//
// Add / edit a user. record: null = closed, {} = add, a user = edit.

import { useState } from 'react';
import { App, Checkbox, Form, Input, Modal, Select, Switch } from 'antd';
import userApi from '../../../services/administration/userApi';
import useModalForm from '../../../hooks/useModalForm';
import handleApiError from '../../../utils/handleApiError';
import applyFormErrors from '../../../utils/formErrors';

const UserFormModal = ({ record, roles, onClose, onSaved }) => {
  const { message } = App.useApp();
  const { form, modalProps, formProps } = useModalForm();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await userApi.update(record.id, values) : await userApi.create(values);
      message.success(isEdit ? 'User updated.' : 'User added.');
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open={!!record} title={isEdit ? 'Edit User' : 'Add User'} keyboard={false} {...modalProps}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form {...formProps} layout="vertical" disabled={saving}
        initialValues={isEdit
          ? { name: record.name, email: record.email, active: record.active, must_change_password: !!record.must_change_password, roles: record.roles?.map((r) => r.id) ?? [] }
          : { active: true, must_change_password: true, roles: [] }}>
        <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter the name.' }]}>
          <Input autoFocus />
        </Form.Item>
        <Form.Item name="email" label="Email"
          rules={[{ required: true, message: 'Enter the email.' }, { type: 'email', message: 'Enter a valid email.' }]}>
          <Input autoComplete="off" />
        </Form.Item>
        <Form.Item name="password" label="Password" extra={isEdit ? 'Leave blank to keep the current password.' : undefined}
          rules={[{ required: !isEdit, message: 'Enter a password.' }, { min: 8, message: 'At least 8 characters.' }]}>
          <Input.Password autoComplete="new-password" />
        </Form.Item>
        <Form.Item name="password_confirmation" label="Confirm password" dependencies={['password']}
          rules={[({ getFieldValue }) => ({
            validator: (_, value) => (!getFieldValue('password') || value === getFieldValue('password')
              ? Promise.resolve() : Promise.reject(new Error('The passwords don\'t match.'))),
          })]}>
          <Input.Password autoComplete="new-password" />
        </Form.Item>
        <Form.Item name="roles" label="Roles">
          <Select mode="multiple" allowClear placeholder="No roles" optionFilterProp="label"
            options={roles.map((r) => ({ value: r.id, label: r.name }))} />
        </Form.Item>
        <Form.Item name="active" label="Active" valuePropName="checked">
          <Switch />
        </Form.Item>
        <Form.Item name="must_change_password" valuePropName="checked" style={{ marginBottom: 0 }}
          extra="Until they do, the user sees only the Change Password screen.">
          <Checkbox>Require password change at next sign-in</Checkbox>
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default UserFormModal;
