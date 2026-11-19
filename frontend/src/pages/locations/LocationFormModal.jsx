// src/pages/locations/LocationFormModal.jsx — add / edit a location. record: null = closed, {} = add, a record = edit.

import { useState } from 'react';
import { App, Col, Form, Input, Modal, Row, Segmented, Switch } from 'antd';
import { forgetLocations } from '../../services/locations/locationCache';
import locationApi from '../../services/locations/locationApi';
import useModalForm from '../../hooks/useModalForm';
import handleApiError from '../../utils/handleApiError';
import applyFormErrors from '../../utils/formErrors';

const LocationFormModal = ({ record, onClose, onSaved }) => {
  const { message } = App.useApp();
  const { form, modalProps, formProps } = useModalForm();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await locationApi.update(record.id, values) : await locationApi.create(values);
      message.success(isEdit ? 'Location updated.' : 'Location added.');
      forgetLocations();
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open={!!record} title={isEdit ? 'Edit Location' : 'Add Location'} keyboard={false} {...modalProps}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form {...formProps} layout="vertical" disabled={saving}
        initialValues={isEdit
          ? { code: record.code, name: record.name, type: record.type, address: record.address, active: record.active }
          : { type: 'store', active: true }}>
        <Form.Item name="type" label="Type">
          <Segmented block options={[{ value: 'store', label: 'Store (sells)' }, { value: 'warehouse', label: 'Warehouse (stock only)' }]} />
        </Form.Item>
        <Row gutter={12}>
          <Col span={8}>
            <Form.Item name="code" label="Code" tooltip="Short label on stock documents, e.g. MAIN, QC1, WH1"
              rules={[{ required: true, message: 'Enter a code.' }, { pattern: /^[A-Za-z0-9_-]+$/, message: 'Letters, digits, - and _ only.' }]}>
              <Input autoFocus maxLength={20} style={{ textTransform: 'uppercase' }} />
            </Form.Item>
          </Col>
          <Col span={16}>
            <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter the name.' }]}>
              <Input placeholder="e.g. SM North Branch" />
            </Form.Item>
          </Col>
        </Row>
        <Form.Item name="address" label="Address">
          <Input.TextArea rows={2} maxLength={500} />
        </Form.Item>
        <Form.Item name="active" label="Active" valuePropName="checked">
          <Switch />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default LocationFormModal;
