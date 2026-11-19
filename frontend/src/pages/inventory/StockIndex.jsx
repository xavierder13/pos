// src/pages/inventory/StockIndex.jsx — /inventory/stock: on hand per location (or all), low / out /
// negative filters, stock value at cost, and the Receive / Adjust / Transfer actions.
// The chosen location is remembered per browser (a store manager mostly looks at their own store).

import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Button, Input, Segmented, Select, Space, Table, Tag, Tooltip } from 'antd';
import { ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../components/PageHeader';
import LocationSelect from '../../components/inputs/LocationSelect';
import usePagedList from '../../hooks/usePagedList';
import useCategoryOptions from '../../hooks/useCategoryOptions';
import inventoryApi from '../../services/inventory/inventoryApi';
import { formatMoney } from '../../utils/money';
import { formatQty } from '../../utils/quantity';
import { COLORS } from '../../utils/theme';
import useInventoryActions from './useInventoryActions';

const LOCATION_KEY = 'inventory_location';
const readLocation = () => {
  try { return Number(localStorage.getItem(LOCATION_KEY)) || null; } catch { return null; }
};

const STATUS = [
  { value: 'all', label: 'All' },
  { value: 'low', label: 'Low' },
  { value: 'out', label: 'Out of stock' },
  { value: 'negative', label: 'Negative' },
];

const StockIndex = () => {
  const [locationId] = useState(readLocation);
  const list = usePagedList(inventoryApi.stock, { location_id: locationId ?? undefined });
  const categories = useCategoryOptions();
  const actions = useInventoryActions({ locationId: list.params.location_id, onPosted: () => list.reload({ page: list.params.page }) });

  const setLocation = (id) => {
    try { localStorage.setItem(LOCATION_KEY, id ?? ''); } catch { /* storage blocked */ }
    list.reload({ location_id: id ?? undefined });
  };

  const sortOrder = (key) => (list.params.sort === key ? (list.params.order === 'asc' ? 'ascend' : 'descend') : null);

  const columns = [
    { title: 'SKU', dataIndex: 'sku', key: 'sku', width: 120, sorter: true, sortOrder: sortOrder('sku') },
    {
      title: 'Product', dataIndex: 'name', key: 'name', sorter: true, sortOrder: sortOrder('name'),
      render: (name, r) => (
        <>
          <Link to={`/products/${r.id}`}>{name}</Link>
          {!r.active && <Tag style={{ marginLeft: 8 }}>Inactive</Tag>}
        </>
      ),
    },
    { title: 'Category', key: 'category', width: 140, render: (_, r) => r.category?.name ?? '—' },
    {
      title: 'On hand', dataIndex: 'quantity', key: 'quantity', width: 140, align: 'right', sorter: true, sortOrder: sortOrder('quantity'),
      render: (q, r) => {
        const state = q < 0 ? 'negative' : q <= 0 ? 'out' : q <= r.reorder_level ? 'low' : null;
        const color = { negative: COLORS.error, out: COLORS.error, low: COLORS.warning }[state];
        return (
          <Space size={6}>
            {state && <Tag color={state === 'low' ? 'warning' : 'error'}>{state === 'low' ? 'Low' : state === 'out' ? 'Out' : 'Negative'}</Tag>}
            <span className="money" style={{ color, fontWeight: 700 }}>{formatQty(q, r.unit)}</span>
          </Space>
        );
      },
    },
    { title: 'Reorder at', dataIndex: 'reorder_level', key: 'reorder_level', width: 110, align: 'right', render: (v) => <span className="money">{formatQty(v)}</span> },
    {
      title: <Tooltip title="On hand × unit cost">Value at cost</Tooltip>, key: 'value', width: 140, align: 'right',
      render: (_, r) => (r.cost && r.quantity > 0 ? <span className="money">{formatMoney(Math.round(r.cost * r.quantity))}</span> : '—'),
    },
    {
      title: '', key: 'history', width: 90,
      render: (_, r) => <Link to={`/inventory/movements?product_id=${r.id}${list.params.location_id ? `&location_id=${list.params.location_id}` : ''}`}>History</Link>,
    },
  ];

  const onTableChange = (pagination, _f, sorter) => {
    const order = sorter?.order ? (sorter.order === 'ascend' ? 'asc' : 'desc') : undefined;
    list.reload({ page: pagination.current, per_page: pagination.pageSize, sort: order ? sorter.columnKey : undefined, order });
  };

  return (
    <>
      <PageHeader title="Stock" subtitle="What's on hand, where" extra={<Space className="stock-actions" wrap>{actions.buttons}</Space>} />

      <Space wrap style={{ marginBottom: 16 }}>
        <LocationSelect allowAll value={list.params.location_id ?? null} onChange={setLocation} style={{ width: 220 }} />
        <Input.Search placeholder="Name, SKU or scan a barcode" allowClear style={{ width: 260 }}
          onSearch={(search) => list.reload({ search: search || undefined })} />
        <Select placeholder="Category" allowClear style={{ width: 170 }} options={categories} value={list.params.category_id}
          showSearch={{ optionFilterProp: 'label' }} onChange={(category_id) => list.reload({ category_id })} />
        <Segmented options={STATUS} value={list.params.status ?? 'all'}
          onChange={(s) => list.reload({ status: s === 'all' ? undefined : s })} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => list.reload({ page: list.params.page })} /></Tooltip>
      </Space>

      <Table rowKey="id" columns={columns} dataSource={list.items} loading={list.loading} onChange={onTableChange} scroll={{ x: 950 }}
        pagination={{ current: list.params.page, pageSize: list.params.per_page, total: list.total, showTotal: (n) => `${n} product${n === 1 ? '' : 's'}` }} />

      {actions.modal}
    </>
  );
};

export default StockIndex;
