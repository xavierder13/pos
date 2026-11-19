// src/components/inputs/MoneyInput.jsx
//
// A peso amount typed in pesos (₱125.50) whose form value is integer centavos (12550), so forms
// send the API exactly what it stores. Use as a Form.Item child: <Form.Item name="price"><MoneyInput /></Form.Item>

import { InputNumber } from 'antd';
import { CURRENCY_SYMBOL, MINOR_DIGITS, toMajor, toMinor } from '../../utils/money';

const MoneyInput = ({ value, onChange, ...rest }) => (
  <InputNumber
    min={0}
    precision={MINOR_DIGITS}
    prefix={CURRENCY_SYMBOL}
    style={{ width: '100%' }}
    className="money"
    value={value === null || value === undefined ? null : toMajor(value)}
    onChange={(v) => onChange?.(v === null ? null : toMinor(v))}
    {...rest}
  />
);

export default MoneyInput;
