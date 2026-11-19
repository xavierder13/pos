// src/pages/register/PaymentModal.jsx
//
// Take payment for the sale: one or more tenders (split payments), quick cash
// buttons (exact, then the next round notes), change due, then "New sale".
// Only cash may go over what is owed (the excess is the change); card and
// e-wallet are capped at the balance. Mounted only while open, so every sale
// starts with no tenders.

import { useMemo, useRef, useState } from 'react';
import { Button, InputNumber, Modal, Segmented } from 'antd';
import { CheckCircleFilled, CreditCardOutlined, DeleteOutlined, MobileOutlined, WalletOutlined } from '@ant-design/icons';
import { CURRENCY_SYMBOL, MINOR_DIGITS, formatMoney, toMajor, toMinor } from '../../utils/money';

const METHODS = [
  { value: 'cash', label: 'Cash', icon: <WalletOutlined /> },
  { value: 'card', label: 'Card', icon: <CreditCardOutlined /> },
  { value: 'ewallet', label: 'E-wallet', icon: <MobileOutlined /> },
];
const methodLabel = (value) => METHODS.find((m) => m.value === value)?.label ?? value;

// exact, then the next amounts a customer is likely to hand over (round notes)
const NOTE_STEPS = [1, 5, 10, 20, 50, 100, 200, 500, 1000];
const quickCash = (dueMinor) => {
  const due = toMajor(dueMinor);
  const out = [dueMinor];
  NOTE_STEPS.forEach((step) => {
    const next = toMinor(Math.ceil(due / step) * step);
    if (next > dueMinor && !out.includes(next)) out.push(next);
  });
  return out.slice(0, 5);
};

const PaymentModal = ({ total, onClose, onComplete }) => {
  const [tenders, setTenders] = useState([]);
  const [method, setMethod] = useState('cash');
  const [amount, setAmount] = useState(() => toMajor(total));
  const [done, setDone] = useState(false);
  const inputRef = useRef(null);

  const paid = tenders.reduce((sum, t) => sum + t.amount, 0);
  const balance = Math.max(0, total - paid);
  const change = Math.max(0, paid - total);
  const quick = useMemo(() => quickCash(balance), [balance]);

  const addTender = (minor, how = method) => {
    if (!minor || minor <= 0 || balance <= 0) return;
    const capped = how === 'cash' ? minor : Math.min(minor, balance);
    const nextPaid = paid + capped;
    setTenders((list) => [...list, { key: list.length + 1, method: how, amount: capped }]);
    setAmount(toMajor(Math.max(0, total - nextPaid)));
    if (nextPaid >= total) setDone(true);
    else inputRef.current?.focus();
  };

  const removeTender = (key) => {
    const list = tenders.filter((t) => t.key !== key);
    setTenders(list);
    setAmount(toMajor(Math.max(0, total - list.reduce((s, t) => s + t.amount, 0))));
  };

  const finish = () => onComplete({ tenders, paid, change });

  return (
    <Modal open centered width={560} keyboard={false} title={done ? null : 'Take payment'}
      onCancel={done ? finish : onClose} footer={null} className="pay-modal">
      {done ? (
        <div className="pay-done">
          <CheckCircleFilled className="pay-done__icon" />
          <div className="pay-done__label">{change > 0 ? 'Change due' : 'Paid in full'}</div>
          <div className="pay-done__amount money">{formatMoney(change)}</div>
          <div className="pay-done__meta">
            {tenders.map((t) => `${methodLabel(t.method)} ${formatMoney(t.amount)}`).join(' · ')}
          </div>
          <Button type="primary" size="large" block autoFocus onClick={finish} className="pay-done__next">
            New sale
          </Button>
        </div>
      ) : (
        <>
          <div className="pay-due">
            <div>
              <span>Total</span>
              <strong className="money">{formatMoney(total)}</strong>
            </div>
            <div className={paid ? 'is-active' : ''}>
              <span>Balance due</span>
              <strong className="money">{formatMoney(balance)}</strong>
            </div>
          </div>

          <Segmented block size="large" value={method} onChange={setMethod} className="pay-methods"
            options={METHODS.map((m) => ({ value: m.value, label: <span className="pay-method">{m.icon}{m.label}</span> }))} />

          {method === 'cash' && (
            <div className="pay-quick">
              {quick.map((value, i) => (
                <Button key={value} size="large" onClick={() => addTender(value, 'cash')}>
                  {i === 0 ? 'Exact' : formatMoney(value)}
                </Button>
              ))}
            </div>
          )}

          <div className="pay-entry">
            <InputNumber ref={inputRef} size="large" autoFocus min={0} precision={MINOR_DIGITS} prefix={CURRENCY_SYMBOL}
              value={amount} onChange={setAmount} style={{ flex: 1 }} className="money"
              onPressEnter={() => addTender(toMinor(amount))} aria-label="Amount tendered" />
            <Button type="primary" size="large" onClick={() => addTender(toMinor(amount))} disabled={!amount}>
              Add {methodLabel(method)}
            </Button>
          </div>

          {tenders.length > 0 && (
            <ul className="pay-tenders">
              {tenders.map((t) => (
                <li key={t.key}>
                  <span>{methodLabel(t.method)}</span>
                  <span className="money">{formatMoney(t.amount)}</span>
                  <Button type="text" size="small" icon={<DeleteOutlined />} aria-label="Remove payment" onClick={() => removeTender(t.key)} />
                </li>
              ))}
            </ul>
          )}
        </>
      )}
    </Modal>
  );
};

export default PaymentModal;
