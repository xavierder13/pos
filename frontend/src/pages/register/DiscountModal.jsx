// src/pages/register/DiscountModal.jsx — a % discount on the whole sale.
// (Manager approval for big discounts comes with the Sales module.)

import { useState } from 'react';
import { Button, InputNumber, Modal } from 'antd';

const PRESETS = [5, 10, 15, 20, 25, 50];

const DiscountModal = ({ current, onApply, onClose }) => {
  const [pct, setPct] = useState(current || null);

  return (
    <Modal open centered width={420} keyboard={false} title="Discount on this sale" onCancel={onClose}
      footer={[
        current > 0 && <Button key="remove" danger onClick={() => onApply(0)}>Remove discount</Button>,
        <Button key="cancel" onClick={onClose}>Cancel</Button>,
        <Button key="apply" type="primary" disabled={!pct} onClick={() => onApply(pct)}>Apply</Button>,
      ]}>
      <div className="disc-presets">
        {PRESETS.map((p) => (
          <Button key={p} size="large" type={pct === p ? 'primary' : 'default'} onClick={() => setPct(p)}>{p}%</Button>
        ))}
      </div>
      <InputNumber size="large" min={0} max={100} precision={2} suffix="%" value={pct} onChange={setPct}
        placeholder="Other %" style={{ width: '100%' }} onPressEnter={() => pct && onApply(pct)} aria-label="Discount percent" />
    </Modal>
  );
};

export default DiscountModal;
