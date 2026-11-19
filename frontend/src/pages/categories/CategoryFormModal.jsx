// src/pages/categories/CategoryFormModal.jsx — add / edit a category. record: null = closed, {} = add, a record = edit.

import { useState } from 'react';
import { App, Form, Input, Modal, Switch } from 'antd';
import categoryApi from '../../services/categories/categoryApi';
import useModalForm from '../../hooks/useModalForm';
import handleApiError from '../../utils/handleApiError';
import applyFormErrors from '../../utils/formErrors';

const CategoryFormModal = ({ record, onClose, onSaved }) => {
  const { message } = App.useApp();
  const { form, modalProps, formProps } = useModalForm();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await categoryApi.update(record.id, values) : await categoryApi.create(values);
      message.success(isEdit ? 'Category updated.' : 'Category added.');
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open={!!record} title={isEdit ? 'Edit Category' : 'Add Category'} keyboard={false} {...modalProps}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form {...formProps} layout="vertical" disabled={saving}
        initialValues={isEdit ? { name: record.name, description: record.description, active: record.active } : { active: true }}>
        <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter the name.' }]}>
          <Input autoFocus />
        </Form.Item>
        <Form.Item name="description" label="Description">
          <Input.TextArea rows={3} showCount maxLength={2000} />
        </Form.Item>
        <Form.Item name="active" label="Active" valuePropName="checked">
          <Switch />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default CategoryFormModal;
