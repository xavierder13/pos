// src/pages/inventory/components/MovementTable.jsx — ledger lines (Movement from inventoryApi).
// `hideProduct` on a product's own page. Quantity is signed and coloured: green in, red out.

import { Link } from 'react-router-dom';
import { Table, Tag, Tooltip } from 'antd';
import { DOC_TYPES, ADJUST_REASONS } from '../../../services/inventory/inventoryApi';
import { formatDateTime } from '../../../utils/formatDate';
import { formatQty } from '../../../utils/quantity';
import { COLORS } from '../../../utils/theme';

const reasonLabel = (r) => ADJUST_REASONS.find((x) => x.value === r)?.label ?? r;

const MovementTable = ({ hideProduct = false, ...tableProps }) => {
  const columns = [
    { title: 'When', dataIndex: 'created_at', key: 'created_at', width: 170, render: formatDateTime },
    {
      title: 'Document', key: 'document', width: 150,
      render: (_, m) => <Link to={`/inventory/documents/${m.document?.id}`}>{m.document?.number}</Link>,
    },
    {
      title: 'Type', key: 'type', width: 160,
      render: (_, m) => {
        const t = DOC_TYPES[m.document?.type];
        const direction = m.document?.type === 'transfer' ? (m.quantity < 0 ? ' out' : ' in') : '';
        return (
          <>
            <Tag color={t?.color}>{t?.label}{direction}</Tag>
            {m.document?.reason && <span style={{ color: COLORS.muted, fontSize: 12 }}>{reasonLabel(m.document.reason)}</span>}
          </>
        );
      },
    },
    ...(hideProduct ? [] : [{
      title: 'Product', key: 'product',
      render: (_, m) => <Link to={`/products/${m.product?.id}`}>{m.product?.sku} · {m.product?.name}</Link>,
    }]),
    { title: 'Location', key: 'location', width: 110, render: (_, m) => <Tooltip title={m.location?.name}><Tag>{m.location?.code}</Tag></Tooltip> },
    {
      title: 'Qty', dataIndex: 'quantity', key: 'quantity', width: 100, align: 'right',
      render: (q, m) => (
        <span className="money" style={{ color: q < 0 ? COLORS.error : COLORS.success, fontWeight: 600 }}>
          {q > 0 ? '+' : ''}{formatQty(q, m.product?.unit)}
        </span>
      ),
    },
    { title: 'Balance', dataIndex: 'balance_after', key: 'balance_after', width: 100, align: 'right', render: (b) => <span className="money">{formatQty(b)}</span> },
    { title: 'By', key: 'user', width: 140, ellipsis: true, render: (_, m) => m.document?.user?.name ?? '—' },
  ];

  return <Table rowKey="id" columns={columns} scroll={{ x: hideProduct ? 900 : 1150 }} size="middle" {...tableProps} />;
};

export default MovementTable;
