// src/pages/products/ProductView.jsx — /products/:id: details, price & tax, stock per location, the
// product's latest stock movements, attachments (photos) and the audit history.

import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { App, Button, Card, Col, Empty, Popconfirm, Row, Spin, Table, Tag } from 'antd';
import { DeleteOutlined, EditOutlined } from '@ant-design/icons';
import ViewHeader from '../../components/ViewHeader';
import ViewSection from '../../components/ViewSection';
import AuditHistory from '../../components/AuditHistory';
import Attachments from '../../components/Attachments';
import RecordNotFound from '../../components/RecordNotFound';
import useAuth from '../../hooks/useAuth';
import productApi from '../../services/products/productApi';
import handleApiError from '../../utils/handleApiError';
import { formatDateTime } from '../../utils/formatDate';
import { formatMoney } from '../../utils/money';
import { formatQty } from '../../utils/quantity';
import { TAX_CLASSES } from '../../utils/tax';
import { COLORS } from '../../utils/theme';
import inventoryApi from '../../services/inventory/inventoryApi';
import MovementTable from '../inventory/components/MovementTable';
import ProductFormModal from './ProductFormModal';

const LIST_PATH = '/products';

const ProductView = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { message } = App.useApp();
  const { can } = useAuth();
  const [record, setRecord] = useState(null);
  const [state, setState] = useState('loading');   // loading | ready | missing
  const [editing, setEditing] = useState(false);
  const [levels, setLevels] = useState([]);
  const [moves, setMoves] = useState({ data: [], loading: true });
  const canSeeStock = can('inventory-list');

  const load = useCallback(async () => {
    try {
      const { data } = await productApi.getById(id);
      setRecord(data.data);
      setState('ready');
      if (data.data.track_stock) {
        inventoryApi.levels(id).then(({ data: l }) => setLevels(l.data)).catch(() => {});
        if (canSeeStock) {
          inventoryApi.movements({ product_id: id, per_page: 10 })
            .then(({ data: m }) => setMoves({ data: m.data, loading: false, total: m.meta.total }))
            .catch(() => setMoves({ data: [], loading: false }));
        }
      }
    } catch (error) {
      if (error.response?.status !== 404) handleApiError(error, message);
      setState((s) => (s === 'loading' ? 'missing' : s));
    }
  }, [id, message, canSeeStock]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const remove = async () => {
    try {
      const { data } = await productApi.remove(record.id);
      message.success(data.message);
      navigate(LIST_PATH);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  if (state === 'loading') return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  if (state === 'missing') return <RecordNotFound title="Product not found" backTo={LIST_PATH} />;

  return (
    <>
      <ViewHeader
        backTo={LIST_PATH}
        title={record.name}
        tags={<Tag color={record.active ? 'green' : 'default'}>{record.active ? 'Active' : 'Inactive'}</Tag>}
        subtitle={`${record.sku}${record.barcode ? ` · ${record.barcode}` : ''}${record.category ? ` · ${record.category.name}` : ''}`}
        extra={<>
          {can('product-edit') && <Button type="primary" icon={<EditOutlined />} onClick={() => setEditing(true)}>Edit</Button>}
          {can('product-delete') && (
            <Popconfirm title="Delete this product?" description={record.name} okText="Delete" okButtonProps={{ danger: true }} onConfirm={remove}>
              <Button danger icon={<DeleteOutlined />}>Delete</Button>
            </Popconfirm>
          )}
        </>}
      />

      <Row gutter={[16, 16]}>
        <Col xs={24} xl={12}>
          <ViewSection title="Details" items={[
            ['Name', record.name],
            ['SKU', record.sku],
            ['Barcode', record.barcode],
            ['Category', record.category?.name],
            ['Unit', record.unit],
            ['Description', record.description],
            ['Status', <Tag key="s" color={record.active ? 'green' : 'default'}>{record.active ? 'Active' : 'Inactive'}</Tag>],
          ]} />
        </Col>
        <Col xs={24} xl={12}>
          <ViewSection title="Price & tax" items={[
            ['Selling price', <span key="p" className="money" style={{ fontWeight: 700 }}>{formatMoney(record.price)}</span>],
            ['Unit cost', record.cost !== null ? <span key="c" className="money">{formatMoney(record.cost)}</span> : null],
            ['Margin', record.price ? record.cost ? `${(((record.price - record.cost) / record.price) * 100).toFixed(1)}%` : '—' : '—'],
            ['Tax class', <Tag key="t" color={TAX_CLASSES[record.tax_class]?.color}>{TAX_CLASSES[record.tax_class]?.label}</Tag>],
            ['Senior / PWD discount', record.sc_pwd_eligible ? 'Applies' : 'No'],
            ['Created', formatDateTime(record.created_at)],
            ['Last updated', formatDateTime(record.updated_at)],
          ]} />
        </Col>
        {record.track_stock ? (
          <>
            <Col xs={24} xl={10}>
              <Card title="Stock by location" extra={<span style={{ color: COLORS.muted }}>Reorder at {formatQty(record.reorder_level)}</span>}>
                <Table rowKey={(r) => r.location.id} size="small" pagination={false} dataSource={levels}
                  summary={(rows) => rows.length > 1 && (
                    <Table.Summary.Row>
                      <Table.Summary.Cell index={0}><strong>Total</strong></Table.Summary.Cell>
                      <Table.Summary.Cell index={1} align="right">
                        <strong className="money">{formatQty(rows.reduce((s, r) => s + r.quantity, 0), record.unit)}</strong>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                  )}
                  columns={[
                    { title: 'Location', key: 'l', render: (_, r) => <><Tag>{r.location.code}</Tag>{r.location.name}</> },
                    {
                      title: 'On hand', dataIndex: 'quantity', align: 'right',
                      render: (q) => (
                        <span className="money" style={{ color: q < 0 ? COLORS.error : q <= record.reorder_level ? COLORS.warning : undefined, fontWeight: 600 }}>
                          {formatQty(q, record.unit)}
                        </span>
                      ),
                    },
                  ]} />
              </Card>
            </Col>
            {canSeeStock && (
              <Col xs={24} xl={14}>
                <Card title="Latest movements" extra={moves.total > 10 && <Link to={`/inventory/movements?product_id=${record.id}`}>All {moves.total}</Link>}>
                  <MovementTable hideProduct dataSource={moves.data} loading={moves.loading} pagination={false}
                    locale={{ emptyText: <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No stock movements yet" /> }} />
                </Card>
              </Col>
            )}
          </>
        ) : (
          <Col xs={24}><Card><Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Not stock-tracked (a service)" /></Card></Col>
        )}
        <Col xs={24} xl={12}>
          <Attachments type="product" recordId={record.id} />
        </Col>
        <Col xs={24}>
          <AuditHistory record="Product" recordId={record.id} />
        </Col>
      </Row>

      <ProductFormModal record={editing ? record : null} onClose={() => setEditing(false)}
        onSaved={() => { setEditing(false); load(); }} />
    </>
  );
};

export default ProductView;
