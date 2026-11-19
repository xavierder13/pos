// src/pages/administration/roles/RoleFormModal.jsx
//
// Add / edit a role and its permissions, grouped by module
// (`user-create` → module "user").

import { useState } from 'react';
import { App, Checkbox, Col, Divider, Form, Input, Modal, Row, Typography } from 'antd';
import roleApi from '../../../services/administration/roleApi';
import useModalForm from '../../../hooks/useModalForm';
import handleApiError from '../../../utils/handleApiError';
import applyFormErrors from '../../../utils/formErrors';
import { groupByModule } from '../../../utils/permissionGroups';

const RoleFormModal = ({ record, permissions, onClose, onSaved }) => {
  const { message } = App.useApp();
  const { form, modalProps, formProps } = useModalForm();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;

  const groups = groupByModule(permissions);

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await roleApi.update(record.id, values) : await roleApi.create(values);
      message.success(isEdit ? 'Role updated.' : 'Role added.');
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open={!!record} title={isEdit ? 'Edit Role' : 'Add Role'} width={720} keyboard={false} {...modalProps}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form {...formProps} layout="vertical" disabled={saving}
        initialValues={{ name: record?.name ?? '', permissions: record?.permissions?.map((p) => p.id) ?? [] }}>
        <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter the role name.' }]}>
          <Input autoFocus />
        </Form.Item>
        <Form.Item name="permissions" label="Permissions">
          <Checkbox.Group style={{ width: '100%' }}>
            {groups.map(({ module, label, permissions: list }) => (
              <div key={module} style={{ width: '100%' }}>
                <Divider titlePlacement="start" style={{ margin: '8px 0' }}>
                  <Typography.Text strong>{label}</Typography.Text>
                </Divider>
                <Row gutter={[8, 4]}>
                  {list.map((p) => (
                    <Col key={p.id} xs={12} sm={8}><Checkbox value={p.id}>{p.action || p.name}</Checkbox></Col>
                  ))}
                </Row>
              </div>
            ))}
          </Checkbox.Group>
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default RoleFormModal;
