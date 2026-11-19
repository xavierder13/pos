// src/pages/inventory/components/StockDocumentModal.jsx
//
// Post a stock document: kind = receive | adjust | transfer. Lines are products + quantities
// (+ unit cost on a receipt). Mounted only while open, with its own form (Form.List in a dialog:
// see frontend/CLAUDE.md), so every open starts empty. `defaults` presets the location(s).
// Adjustments take signed quantities: + found, − damaged/lost. The API refuses going below zero.

import { useState } from 'react';
import { App, Button, Col, Empty, Form, Input, InputNumber, Modal, Row, Select, Typography } from 'antd';
import { DeleteOutlined, PlusOutlined, SwapRightOutlined } from '@ant-design/icons';
import inventoryApi, { ADJUST_REASONS } from '../../../services/inventory/inventoryApi';
import LocationSelect from '../../../components/inputs/LocationSelect';
import ProductPicker from '../../../components/inputs/ProductPicker';
import MoneyInput from '../../../components/inputs/MoneyInput';
import handleApiError from '../../../utils/handleApiError';
import applyFormErrors from '../../../utils/formErrors';
import { formatMoney } from '../../../utils/money';

const KINDS = {
  receive: { title: 'Receive stock', ok: 'Post receipt', call: inventoryApi.receive, hint: 'Goods delivered by a supplier.' },
  adjust: { title: 'Adjust stock', ok: 'Post adjustment', call: inventoryApi.adjust, hint: 'Use + for stock found, − for damaged, expired or lost.' },
  transfer: { title: 'Transfer stock', ok: 'Post transfer', call: inventoryApi.transfer, hint: 'Moves stock between locations in one step.' },
};

const StockDocumentModal = ({ kind, defaults = {}, onClose, onPosted }) => {
  const { message } = App.useApp();
  const [form] = Form.useForm();
  const [saving, setSaving] = useState(false);
  const [units, setUnits] = useState({});   // product id → unit, for the quantity suffix
  const cfg = KINDS[kind];
  const from = Form.useWatch('location_id', form);
  const lines = Form.useWatch('lines', form) || [];
  const receiptTotal = kind === 'receive'
    ? lines.reduce((sum, l) => sum + (l?.unit_cost && l?.quantity ? Math.round(l.unit_cost * l.quantity) : 0), 0)
    : 0;

  const post = async () => {
    try {
      const values = await form.validateFields();
      setSaving(true);
      const { data } = await cfg.call(values);
      message.success(data.message);
      onPosted(data.data);
    } catch (error) {
      if (!applyFormErrors(form, error)) handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open title={cfg.title} width={860} keyboard={false} onCancel={onClose} onOk={post}
      okText={cfg.ok} confirmLoading={saving}>
      <Typography.Paragraph type="secondary" style={{ marginTop: -4 }}>{cfg.hint}</Typography.Paragraph>
      <Form form={form} layout="vertical" disabled={saving}
        initialValues={{ location_id: defaults.location_id, to_location_id: defaults.to_location_id, lines: [{}] }}>
        <Row gutter={12}>
          <Col xs={24} sm={kind === 'transfer' ? 11 : 8}>
            <Form.Item name="location_id" label={kind === 'transfer' ? 'From' : 'Location'} rules={[{ required: true, message: 'Choose a location.' }]}>
              <LocationSelect />
            </Form.Item>
          </Col>
          {kind === 'transfer' && (
            <>
              <Col xs={0} sm={2} style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', paddingTop: 8 }}>
                <SwapRightOutlined style={{ fontSize: 20 }} />
              </Col>
              <Col xs={24} sm={11}>
                <Form.Item name="to_location_id" label="To" rules={[{ required: true, message: 'Choose where it goes.' }]}>
                  <LocationSelect exclude={from} />
                </Form.Item>
              </Col>
            </>
          )}
          {kind === 'receive' && (
            <>
              <Col xs={24} sm={9}>
                <Form.Item name="supplier" label="Supplier"><Input maxLength={255} /></Form.Item>
              </Col>
              <Col xs={24} sm={7}>
                <Form.Item name="reference" label="DR / invoice no."><Input maxLength={100} /></Form.Item>
              </Col>
            </>
          )}
          {kind === 'adjust' && (
            <Col xs={24} sm={8}>
              <Form.Item name="reason" label="Reason" rules={[{ required: true, message: 'Choose a reason.' }]}>
                <Select options={ADJUST_REASONS} />
              </Form.Item>
            </Col>
          )}
        </Row>

        <Form.List name="lines" rules={[{ validator: async (_, v) => { if (!v?.length) throw new Error('Add at least one product.'); } }]}>
          {(fields, { add, remove }, { errors }) => (
            <div className="doc-lines">
              {fields.length === 0 && <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No products yet" />}
              {fields.map((field, i) => (
                <Row key={field.key} gutter={8} align="top">
                  <Col xs={24} sm={kind === 'receive' ? 11 : 15}>
                    <Form.Item name={[field.name, 'product_id']} label={i === 0 ? 'Product' : null} rules={[{ required: true, message: 'Choose a product.' }]}>
                      <ProductPicker onPick={(p) => p && setUnits((u) => ({ ...u, [p.id]: p.unit }))} />
                    </Form.Item>
                  </Col>
                  <Col xs={10} sm={kind === 'receive' ? 5 : 7}>
                    <Form.Item name={[field.name, 'quantity']} label={i === 0 ? (kind === 'adjust' ? 'Qty (±)' : 'Quantity') : null}
                      rules={[{ required: true, message: 'Quantity?' }, {
                        validator: async (_, v) => {
                          if (v === 0) throw new Error('Not zero.');
                          if (kind !== 'adjust' && v < 0) throw new Error('Must be positive.');
                        },
                      }]}>
                      <InputNumber precision={3} style={{ width: '100%' }} className="money"
                        min={kind === 'adjust' ? undefined : 0}
                        suffix={units[form.getFieldValue(['lines', field.name, 'product_id'])]} />
                    </Form.Item>
                  </Col>
                  {kind === 'receive' && (
                    <Col xs={10} sm={6}>
                      <Form.Item name={[field.name, 'unit_cost']} label={i === 0 ? 'Unit cost' : null}>
                        <MoneyInput />
                      </Form.Item>
                    </Col>
                  )}
                  <Col xs={4} sm={2}>
                    <Form.Item label={i === 0 ? ' ' : null}>
                      <Button type="text" danger icon={<DeleteOutlined />} onClick={() => remove(field.name)} aria-label="Remove line" />
                    </Form.Item>
                  </Col>
                </Row>
              ))}
              <Form.ErrorList errors={errors} />
              <Button type="dashed" icon={<PlusOutlined />} onClick={() => add({})} block>Add product</Button>
            </div>
          )}
        </Form.List>

        {kind === 'receive' && receiptTotal > 0 && (
          <div className="doc-total"><span>Total cost</span><strong className="money">{formatMoney(receiptTotal)}</strong></div>
        )}
        <Form.Item name="note" label="Note" style={{ marginTop: 16 }}>
          <Input.TextArea rows={2} maxLength={1000} />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default StockDocumentModal;
