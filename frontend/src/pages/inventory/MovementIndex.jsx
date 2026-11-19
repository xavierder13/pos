// src/pages/inventory/MovementIndex.jsx — /inventory/movements: the stock ledger, newest first.
// ?product_id= and ?location_id= (from Stock / a product page) open it filtered.

import { useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Button, DatePicker, Select, Space, Tag, Tooltip } from 'antd';
import { CloseOutlined, ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../components/PageHeader';
import LocationSelect from '../../components/inputs/LocationSelect';
import ProductPicker from '../../components/inputs/ProductPicker';
import usePagedList from '../../hooks/usePagedList';
import inventoryApi, { DOC_TYPES } from '../../services/inventory/inventoryApi';
import MovementTable from './components/MovementTable';

const MovementIndex = () => {
  const [searchParams] = useSearchParams();
  const [initial] = useState(() => ({
    product_id: Number(searchParams.get('product_id')) || undefined,
    location_id: Number(searchParams.get('location_id')) || undefined,
  }));
  const list = usePagedList(inventoryApi.movements, initial);
  const filteredProduct = list.params.product_id && list.items[0]?.product?.id === list.params.product_id ? list.items[0].product : null;

  return (
    <>
      <PageHeader title="Stock movements" subtitle="Every change to stock, newest first" />

      <Space wrap style={{ marginBottom: 16 }}>
        {list.params.product_id && !filteredProduct ? (
          <Tag closable onClose={() => list.reload({ product_id: undefined })}>Product #{list.params.product_id}</Tag>
        ) : null}
        {filteredProduct ? (
          <Tag closeIcon={<CloseOutlined />} closable onClose={() => list.reload({ product_id: undefined })} style={{ padding: '4px 8px' }}>
            {filteredProduct.sku} · {filteredProduct.name}
          </Tag>
        ) : (
          <ProductPicker trackedOnly allowClear style={{ width: 280 }} placeholder="Filter by product"
            onChange={(id) => list.reload({ product_id: id ?? undefined })} />
        )}
        <LocationSelect allowAll value={list.params.location_id ?? null} onChange={(id) => list.reload({ location_id: id ?? undefined })} style={{ width: 220 }} />
        <Select placeholder="Type" allowClear style={{ width: 150 }} value={list.params.type}
          options={Object.entries(DOC_TYPES).map(([value, t]) => ({ value, label: t.label }))}
          onChange={(type) => list.reload({ type })} />
        <DatePicker.RangePicker allowEmpty={[true, true]}
          onChange={(range) => list.reload({ date_from: range?.[0]?.format('YYYY-MM-DD'), date_to: range?.[1]?.format('YYYY-MM-DD') })} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => list.reload({ page: list.params.page })} /></Tooltip>
      </Space>

      <MovementTable dataSource={list.items} loading={list.loading}
        onChange={(p) => list.reload({ page: p.current, per_page: p.pageSize })}
        pagination={{ current: list.params.page, pageSize: list.params.per_page, total: list.total, showTotal: (n) => `${n} movement${n === 1 ? '' : 's'}` }} />
    </>
  );
};

export default MovementIndex;
