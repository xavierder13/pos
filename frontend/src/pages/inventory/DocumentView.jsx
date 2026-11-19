// src/pages/inventory/DocumentView.jsx — /inventory/documents/:id: one posted stock document and its lines.

import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { App, Card, Col, Row, Spin, Table, Tag } from 'antd';
import ViewHeader from '../../components/ViewHeader';
import ViewSection from '../../components/ViewSection';
import RecordNotFound from '../../components/RecordNotFound';
import inventoryApi, { ADJUST_REASONS, DOC_TYPES } from '../../services/inventory/inventoryApi';
import handleApiError from '../../utils/handleApiError';
import { formatDateTime } from '../../utils/formatDate';
import { formatMoney } from '../../utils/money';
import { formatQty } from '../../utils/quantity';
import { COLORS } from '../../utils/theme';

const LIST_PATH = '/inventory/documents';

const DocumentView = () => {
  const { id } = useParams();
  const { message } = App.useApp();
  const [doc, setDoc] = useState(null);
  const [state, setState] = useState('loading');

  useEffect(() => {
    let alive = true;
    inventoryApi.document(id)
      .then(({ data }) => { if (alive) { setDoc(data.data); setState('ready'); } })
      .catch((error) => {
        if (error.response?.status !== 404) handleApiError(error, message);
        if (alive) setState('missing');
      });
    return () => { alive = false; };
  }, [id, message]);

  if (state === 'loading') return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  if (state === 'missing') return <RecordNotFound title="Document not found" backTo={LIST_PATH} />;

  const type = DOC_TYPES[doc.type];
  const isReceipt = doc.type === 'receipt' || doc.type === 'opening';
  const total = doc.movements.reduce((s, m) => s + (m.unit_cost ? Math.round(m.unit_cost * m.quantity) : 0), 0);
  // a transfer has an out and an in line per product: show the destination line only once per product
  const lines = doc.type === 'transfer' ? doc.movements.filter((m) => m.quantity < 0) : doc.movements;

  const columns = [
    { title: 'Product', key: 'product', render: (_, m) => <Link to={`/products/${m.product.id}`}>{m.product.sku} · {m.product.name}</Link> },
    ...(doc.type === 'transfer' ? [] : [{ title: 'Location', key: 'loc', width: 100, render: (_, m) => <Tag>{m.location.code}</Tag> }]),
    {
      title: 'Quantity', dataIndex: 'quantity', key: 'q', width: 130, align: 'right',
      render: (q, m) => {
        const shown = doc.type === 'transfer' ? -q : q;
        return <span className="money" style={{ fontWeight: 700, color: doc.type === 'adjustment' ? (q < 0 ? COLORS.error : COLORS.success) : undefined }}>
          {doc.type === 'adjustment' && q > 0 ? '+' : ''}{formatQty(shown, m.product.unit)}
        </span>;
      },
    },
    ...(isReceipt ? [
      { title: 'Unit cost', dataIndex: 'unit_cost', key: 'c', width: 130, align: 'right', render: (c) => (c !== null ? <span className="money">{formatMoney(c)}</span> : '—') },
      { title: 'Line total', key: 't', width: 140, align: 'right', render: (_, m) => (m.unit_cost !== null ? <span className="money">{formatMoney(Math.round(m.unit_cost * m.quantity))}</span> : '—') },
    ] : []),
    ...(doc.type === 'transfer' ? [] : [{ title: 'Balance after', dataIndex: 'balance_after', key: 'b', width: 130, align: 'right', render: (b) => <span className="money">{formatQty(b)}</span> }]),
  ];

  return (
    <>
      <ViewHeader backTo={LIST_PATH} title={doc.number} tags={<Tag color={type?.color}>{type?.label}</Tag>}
        subtitle={`Posted ${formatDateTime(doc.created_at)} by ${doc.user?.name ?? '—'}`} />
      <Row gutter={[16, 16]}>
        <Col xs={24} xl={8}>
          <ViewSection title="Details" items={[
            [doc.type === 'transfer' ? 'From' : 'Location', `${doc.location.code} · ${doc.location.name}`],
            ...(doc.to_location ? [['To', `${doc.to_location.code} · ${doc.to_location.name}`]] : []),
            ...(doc.reason ? [['Reason', ADJUST_REASONS.find((r) => r.value === doc.reason)?.label ?? doc.reason]] : []),
            ...(doc.supplier ? [['Supplier', doc.supplier]] : []),
            ...(doc.reference ? [['Reference', doc.reference]] : []),
            ['Note', doc.note],
            ...(isReceipt && total ? [['Total cost', <strong key="t" className="money">{formatMoney(total)}</strong>]] : []),
          ]} />
        </Col>
        <Col xs={24} xl={16}>
          <Card title={`Lines (${lines.length})`}>
            <Table rowKey="id" size="middle" columns={columns} dataSource={lines} pagination={false} scroll={{ x: 600 }} />
          </Card>
        </Col>
      </Row>
    </>
  );
};

export default DocumentView;
