// src/pages/inventory/DocumentIndex.jsx — /inventory/documents: every receipt, adjustment, transfer and
// opening balance, newest first. A posted document is final (fix mistakes with another adjustment).

import { Link, useNavigate } from 'react-router-dom';
import { Button, Input, Select, Space, Table, Tag, Tooltip } from 'antd';
import { ReloadOutlined, SwapRightOutlined } from '@ant-design/icons';
import PageHeader from '../../components/PageHeader';
import LocationSelect from '../../components/inputs/LocationSelect';
import usePagedList from '../../hooks/usePagedList';
import inventoryApi, { ADJUST_REASONS, DOC_TYPES } from '../../services/inventory/inventoryApi';
import { formatDateTime } from '../../utils/formatDate';
import { COLORS } from '../../utils/theme';
import useInventoryActions from './useInventoryActions';

const DocumentIndex = () => {
  const navigate = useNavigate();
  const list = usePagedList(inventoryApi.documents);
  const actions = useInventoryActions({ locationId: list.params.location_id, onPosted: (doc) => navigate(`/inventory/documents/${doc.id}`) });

  const columns = [
    { title: 'Number', dataIndex: 'number', key: 'number', width: 130, render: (n, d) => <Link to={`/inventory/documents/${d.id}`}>{n}</Link> },
    { title: 'Type', dataIndex: 'type', key: 'type', width: 120, render: (t) => <Tag color={DOC_TYPES[t]?.color}>{DOC_TYPES[t]?.label}</Tag> },
    {
      title: 'Location', key: 'location', width: 190,
      render: (_, d) => (
        <Space size={4}>
          <Tooltip title={d.location?.name}><Tag>{d.location?.code}</Tag></Tooltip>
          {d.to_location && <><SwapRightOutlined /><Tooltip title={d.to_location.name}><Tag>{d.to_location.code}</Tag></Tooltip></>}
        </Space>
      ),
    },
    {
      title: 'Details', key: 'details', ellipsis: true,
      render: (_, d) => [
        d.reason && ADJUST_REASONS.find((r) => r.value === d.reason)?.label,
        d.supplier,
        d.reference && `Ref ${d.reference}`,
        d.note,
      ].filter(Boolean).join(' · ') || <span style={{ color: COLORS.faint }}>—</span>,
    },
    { title: 'Lines', dataIndex: 'lines_count', key: 'lines', width: 80, align: 'right' },
    { title: 'By', key: 'user', width: 150, ellipsis: true, render: (_, d) => d.user?.name },
    { title: 'Posted', dataIndex: 'created_at', key: 'created_at', width: 170, render: formatDateTime },
  ];

  return (
    <>
      <PageHeader title="Stock documents" subtitle="Receipts, adjustments and transfers" extra={<Space className="stock-actions" wrap>{actions.buttons}</Space>} />

      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Number, supplier or reference" allowClear style={{ width: 260 }}
          onSearch={(search) => list.reload({ search: search || undefined })} />
        <Select placeholder="Type" allowClear style={{ width: 150 }} value={list.params.type}
          options={Object.entries(DOC_TYPES).map(([value, t]) => ({ value, label: t.label }))}
          onChange={(type) => list.reload({ type })} />
        <LocationSelect allowAll value={list.params.location_id ?? null} onChange={(id) => list.reload({ location_id: id ?? undefined })} style={{ width: 220 }} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => list.reload({ page: list.params.page })} /></Tooltip>
      </Space>

      <Table rowKey="id" columns={columns} dataSource={list.items} loading={list.loading} scroll={{ x: 1000 }}
        onChange={(p) => list.reload({ page: p.current, per_page: p.pageSize })}
        pagination={{ current: list.params.page, pageSize: list.params.per_page, total: list.total, showTotal: (n) => `${n} document${n === 1 ? '' : 's'}` }} />

      {actions.modal}
    </>
  );
};

export default DocumentIndex;
