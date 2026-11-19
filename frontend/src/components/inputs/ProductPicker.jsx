// src/components/inputs/ProductPicker.jsx
//
// Search products by name, SKU or exact barcode (server-side) and pick one. Scanning a barcode into
// it works too. onPick(product) gets the whole product; value is the product id.

import { useEffect, useRef, useState } from 'react';
import { Select, Spin } from 'antd';
import productApi from '../../services/products/productApi';

const ProductPicker = ({ value, onChange, onPick, placeholder = 'Search name, SKU or scan barcode', trackedOnly = true, ...rest }) => {
  const [options, setOptions] = useState([]);
  const [loading, setLoading] = useState(false);
  const timer = useRef(null);
  const found = useRef(new Map());

  const search = (text) => {
    clearTimeout(timer.current);
    timer.current = setTimeout(async () => {
      setLoading(true);
      try {
        const { data } = await productApi.getAll({ search: text || undefined, active: 1, per_page: 20 });
        const list = data.data.filter((p) => !trackedOnly || p.track_stock);
        list.forEach((p) => found.current.set(p.id, p));
        setOptions(list.map((p) => ({ value: p.id, label: `${p.sku} · ${p.name}` })));
      } catch {
        setOptions([]);
      } finally {
        setLoading(false);
      }
    }, 250);
  };

  useEffect(() => () => clearTimeout(timer.current), []);

  return (
    <Select
      showSearch={{ filterOption: false, onSearch: search }}
      value={value}
      placeholder={placeholder}
      options={options}
      notFoundContent={loading ? <Spin size="small" /> : 'No match'}
      onFocus={() => !options.length && search('')}
      onChange={(id) => { onChange?.(id); onPick?.(found.current.get(id)); }}
      {...rest}
    />
  );
};

export default ProductPicker;
