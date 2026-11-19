// src/pages/products/ProductFormModal.jsx — add / edit a product. record: null = closed, {} = add, a record = edit.
// Price and cost are typed in pesos and sent as integer centavos (MoneyInput).

import { useState } from 'react';
import { App, Col, Divider, Form, Input, InputNumber, Modal, Row, Select, Switch } from 'antd';
import { BarcodeOutlined } from '@ant-design/icons';
import productApi from '../../services/products/productApi';
import useModalForm from '../../hooks/useModalForm';
import useCategoryOptions from '../../hooks/useCategoryOptions';
import MoneyInput from '../../components/inputs/MoneyInput';
import handleApiError from '../../utils/handleApiError';
import applyFormErrors from '../../utils/formErrors';
import { TAX_CLASS_OPTIONS } from '../../utils/tax';
import { UNITS } from '../../utils/quantity';

const FIELDS = ['sku', 'barcode', 'name', 'description', 'category_id', 'unit', 'price', 'cost', 'tax_class',
  'sc_pwd_eligible', 'track_stock', 'reorder_level', 'active'];

const ProductFormModal = ({ record, onClose, onSaved }) => {
  const { message } = App.useApp();
  const { form, modalProps, formProps } = useModalForm();
  const categories = useCategoryOptions();
  const [saving, setSaving] = useState(false);
  const isEdit = !!record?.id;
  const tracked = Form.useWatch('track_stock', form);

  const save = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = isEdit ? await productApi.update(record.id, values) : await productApi.create(values);
      message.success(isEdit ? 'Product updated.' : 'Product added.');
      onSaved(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open={!!record} title={isEdit ? 'Edit Product' : 'Add Product'} keyboard={false} width={720} {...modalProps}
      okText={isEdit ? 'Save' : 'Add'} onOk={save} confirmLoading={saving} onCancel={onClose}>
      <Form {...formProps} layout="vertical" disabled={saving}
        initialValues={isEdit
          ? Object.fromEntries(FIELDS.map((f) => [f, record[f]]))
          : { unit: 'pc', tax_class: 'vatable', track_stock: true, sc_pwd_eligible: false, reorder_level: 0, active: true }}>
        <Row gutter={12}>
          <Col xs={24} sm={14}>
            <Form.Item name="name" label="Name" rules={[{ required: true, message: 'Enter the name.' }]}>
              <Input autoFocus placeholder="e.g. Jasmine Rice 2kg" />
            </Form.Item>
          </Col>
          <Col xs={24} sm={10}>
            <Form.Item name="category_id" label="Category">
              <Select allowClear options={categories} placeholder="None" showSearch={{ optionFilterProp: 'label' }} />
            </Form.Item>
          </Col>
          <Col xs={12} sm={8}>
            <Form.Item name="sku" label="SKU" rules={[{ required: true, message: 'Enter the SKU.' }]}>
              <Input maxLength={64} style={{ textTransform: 'uppercase' }} />
            </Form.Item>
          </Col>
          <Col xs={12} sm={10}>
            <Form.Item name="barcode" label="Barcode" tooltip="Scan it into this box">
              <Input maxLength={64} prefix={<BarcodeOutlined />} />
            </Form.Item>
          </Col>
          <Col xs={12} sm={6}>
            <Form.Item name="unit" label="Unit">
              <Select options={UNITS.map((u) => ({ value: u, label: u }))} />
            </Form.Item>
          </Col>
        </Row>

        <Divider titlePlacement="start" plain>Price &amp; tax</Divider>
        <Row gutter={12}>
          <Col xs={12} sm={6}>
            <Form.Item name="price" label="Selling price" tooltip="VAT-inclusive for VATable items" rules={[{ required: true, message: 'Enter the price.' }]}>
              <MoneyInput />
            </Form.Item>
          </Col>
          <Col xs={12} sm={6}>
            <Form.Item name="cost" label="Unit cost" tooltip="Updated automatically from the last receipt">
              <MoneyInput />
            </Form.Item>
          </Col>
          <Col xs={12} sm={6}>
            <Form.Item name="tax_class" label="Tax class">
              <Select options={TAX_CLASS_OPTIONS} />
            </Form.Item>
          </Col>
          <Col xs={12} sm={6}>
            <Form.Item name="sc_pwd_eligible" label="Senior / PWD discount" valuePropName="checked"
              tooltip="20% discount + VAT exemption applies (RA 9994 / RA 10754)">
              <Switch />
            </Form.Item>
          </Col>
        </Row>

        <Divider titlePlacement="start" plain>Stock</Divider>
        <Row gutter={12}>
          <Col xs={12} sm={8}>
            <Form.Item name="track_stock" label="Track stock" valuePropName="checked" tooltip="Off for services (delivery, gift wrapping)">
              <Switch />
            </Form.Item>
          </Col>
          <Col xs={12} sm={8}>
            <Form.Item name="reorder_level" label="Reorder level" tooltip="Low-stock alert at or below this, per location">
              <InputNumber min={0} precision={3} style={{ width: '100%' }} disabled={!tracked} />
            </Form.Item>
          </Col>
          <Col xs={12} sm={8}>
            <Form.Item name="active" label="Active" valuePropName="checked" tooltip="Inactive products disappear from the register">
              <Switch />
            </Form.Item>
          </Col>
        </Row>
        <Form.Item name="description" label="Description">
          <Input.TextArea rows={2} showCount maxLength={2000} />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default ProductFormModal;
