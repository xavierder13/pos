// src/pages/products/ProductIndex.jsx — Products: search by name / SKU / scanned barcode, filter by category,
// price + tax class + stock on hand (all locations) at a glance. ?category_id= opens it filtered.

import { useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { App, Button, Input, Popconfirm, Select, Space, Table, Tag, Tooltip } from 'antd';
import { PlusOutlined, ReloadOutlined } from '@ant-design/icons';
import ActionButton from '../../components/ActionButton';
import PageHeader from '../../components/PageHeader';
import useAuth from '../../hooks/useAuth';
import useProducts from '../../hooks/useProducts';
import productApi from '../../services/products/productApi';
import handleApiError from '../../utils/handleApiError';
import useCategoryOptions from '../../hooks/useCategoryOptions';
import { formatMoney } from '../../utils/money';
import { formatQty } from '../../utils/quantity';
import { TAX_CLASSES } from '../../utils/tax';
import { COLORS } from '../../utils/theme';
import ProductFormModal from './ProductFormModal';

const SORT_ORDER = { asc: 'ascend', desc: 'descend' };

const ProductIndex = () => {
  const { message } = App.useApp();
  const navigate = useNavigate();
  const { can } = useAuth();
  const canCreate = can('product-create');
  const canEdit = can('product-edit');
  const canDelete = can('product-delete');
  const { items, total, params, isLoading, fetchItems } = useProducts();
  const [editing, setEditing] = useState(null);   // null = closed, {} = add, a record = edit
  const categories = useCategoryOptions();
  const [searchParams, setSearchParams] = useSearchParams();

  // a link like /products?category_id=3 (from Categories) filters the list once
  useEffect(() => {
    const id = Number(searchParams.get('category_id'));
    if (id) {
      fetchItems({ category_id: id, page: 1 });
      setSearchParams({}, { replace: true });
    }
  }, [searchParams, setSearchParams, fetchItems]);

  const remove = async (record) => {
    try {
      const { data } = await productApi.remove(record.id);
      message.success(data.message);
      fetchItems(items.length === 1 && params.page > 1 ? { page: params.page - 1 } : {});
    } catch (error) {
      handleApiError(error, message);
    }
  };

  const sortOrder = (key) => (params.sort === key ? SORT_ORDER[params.order] : null);

  const columns = [
    { title: 'SKU', dataIndex: 'sku', key: 'sku', width: 120, sorter: true, sortOrder: sortOrder('sku') },
    {
      title: 'Name', dataIndex: 'name', key: 'name', sorter: true, sortOrder: sortOrder('name'),
      render: (name, record) => (
        <div>
          <Link to={`/products/${record.id}`}>{name}</Link>
          {record.barcode && <div style={{ color: COLORS.faint, fontSize: 12 }}>{record.barcode}</div>}
        </div>
      ),
    },
    { title: 'Category', key: 'category', width: 140, render: (_, r) => r.category?.name ?? '—' },
    {
      title: 'Price', dataIndex: 'price', key: 'price', width: 120, align: 'right', sorter: true, sortOrder: sortOrder('price'),
      render: (v) => <span className="money">{formatMoney(v)}</span>,
    },
    {
      title: 'Tax', dataIndex: 'tax_class', key: 'tax_class', width: 120,
      render: (t, r) => (
        <Space size={4}>
          <Tag color={TAX_CLASSES[t]?.color}>{TAX_CLASSES[t]?.label ?? t}</Tag>
          {r.sc_pwd_eligible && <Tooltip title="Senior / PWD discount applies"><Tag>SC/PWD</Tag></Tooltip>}
        </Space>
      ),
    },
    {
      title: 'On hand', dataIndex: 'on_hand', key: 'on_hand', width: 120, align: 'right',
      render: (qty, r) => {
        if (!r.track_stock) return <Tooltip title="Not stock-tracked (a service)"><span style={{ color: COLORS.faint }}>n/a</span></Tooltip>;
        const low = qty <= r.reorder_level;
        return (
          <Tooltip title={low ? `At or below the reorder level (${formatQty(r.reorder_level)})` : 'All locations'}>
            <span className="money" style={{ color: qty < 0 ? COLORS.error : low ? COLORS.warning : undefined, fontWeight: low ? 700 : 400 }}>
              {formatQty(qty, r.unit)}
            </span>
          </Tooltip>
        );
      },
    },
    {
      title: 'Status', dataIndex: 'active', key: 'active', width: 100, sorter: true, sortOrder: sortOrder('active'),
      render: (active) => <Tag color={active ? 'green' : 'default'}>{active ? 'Active' : 'Inactive'}</Tag>,
    },
    {
      title: 'Actions', key: 'actions', width: 130,
      render: (_, record) => (
        <Space size={6}>
          <ActionButton kind="view" onClick={() => navigate(`/products/${record.id}`)} />
          {canEdit && <ActionButton kind="edit" onClick={() => setEditing(record)} />}
          {canDelete && (
            <Popconfirm title="Delete this product?" description={record.name} okText="Delete" okButtonProps={{ danger: true }} onConfirm={() => remove(record)}>
              <ActionButton kind="delete" />
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ];

  const onTableChange = (pagination, _filters, sorter) => {
    const order = sorter?.order ? (sorter.order === 'ascend' ? 'asc' : 'desc') : undefined;
    fetchItems({ page: pagination.current, per_page: pagination.pageSize, sort: order ? sorter.columnKey : undefined, order });
  };

  return (
    <>
      <PageHeader title="Products" extra={canCreate && (
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setEditing({})}>Add Product</Button>
      )} />

      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Name, SKU or scan a barcode" allowClear style={{ width: 280 }}
          defaultValue={params.search} onSearch={(search) => fetchItems({ search, page: 1 })} />
        <Select placeholder="Category" allowClear style={{ width: 180 }} value={params.category_id} options={categories}
          showSearch={{ optionFilterProp: 'label' }} onChange={(category_id) => fetchItems({ category_id, page: 1 })} />
        <Select placeholder="Status" allowClear style={{ width: 140 }} value={params.active}
          options={[{ value: 1, label: 'Active' }, { value: 0, label: 'Inactive' }]}
          onChange={(active) => fetchItems({ active, page: 1 })} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => fetchItems()} /></Tooltip>
      </Space>

      <Table rowKey="id" columns={columns} dataSource={items} loading={isLoading} onChange={onTableChange} scroll={{ x: 1000 }}
        pagination={{ current: params.page, pageSize: params.per_page, total, showTotal: (n) => `${n} product${n === 1 ? '' : 's'}` }} />

      <ProductFormModal record={editing} onClose={() => setEditing(null)} onSaved={() => { setEditing(null); fetchItems(); }} />
    </>
  );
};

export default ProductIndex;
