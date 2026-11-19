// src/pages/approvals/ApprovalIndex.jsx
//
// Approvals: what waits for my decision, what I requested, and (approval-list)
// every request. Click a row to open the request.

import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { App, Badge, Button, Segmented, Select, Space, Table, Tag, Tooltip, Typography } from 'antd';
import ActionButton from '../../components/ActionButton';
import { ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../components/PageHeader';
import useAuth from '../../hooks/useAuth';
import approvalApi from '../../services/approvalApi';
import handleApiError from '../../utils/handleApiError';
import { formatDateTime } from '../../utils/formatDate';
import { STATUS } from '../../components/approvals/approvalStatus';

const ApprovalIndex = () => {
  const { message } = App.useApp();
  const navigate = useNavigate();
  const { can } = useAuth();
  const [search, setSearch] = useSearchParams();
  const scope = search.get('scope') || 'awaiting';
  const [status, setStatus] = useState();
  const [page, setPage] = useState(1);
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [awaiting, setAwaiting] = useState(0);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await approvalApi.getAll({ scope, status, page, per_page: 20 });
      setRows(data.data);
      setTotal(data.meta.total);
      setAwaiting(data.awaiting_count);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [scope, status, page, message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const columns = [
    {
      title: 'Record', key: 'record',
      render: (_, r) => (r.record ? (
        <div>
          <Link to={`/approvals/${r.id}`}>{r.record.title}</Link>
          <div><Typography.Text type="secondary" style={{ fontSize: 12 }}>{r.record.label} · {r.flow}</Typography.Text></div>
        </div>
      ) : <Typography.Text type="secondary">Deleted record</Typography.Text>),
    },
    { title: 'Requested by', dataIndex: 'requested_by', width: 170, render: (v) => v || '—' },
    { title: 'Level', key: 'level', width: 90, render: (_, r) => `${Math.min(r.current_level, r.total_levels)} / ${r.total_levels}` },
    {
      title: 'Status', dataIndex: 'status', width: 140,
      render: (s, r) => <Space size={4}><Tag color={STATUS[s]?.color}>{STATUS[s]?.label}</Tag>{r.can_decide && <Tag color="cyan">Your turn</Tag>}</Space>,
    },
    { title: 'Submitted', dataIndex: 'created_at', width: 190, render: formatDateTime },
    {
      title: 'Actions', key: 'actions', width: 80,
      render: (_, r) => <ActionButton kind="view" onClick={() => navigate(`/approvals/${r.id}`)} />,
    },
  ];

  const options = [
    { value: 'awaiting', label: <Space size={6}>Awaiting me<Badge count={awaiting} size="small" /></Space> },
    { value: 'requested', label: 'My requests' },
    ...(can('approval-list') ? [{ value: 'all', label: 'All requests' }] : []),
  ];

  return (
    <>
      <PageHeader title="Approvals" subtitle={awaiting ? `${awaiting} waiting for your decision` : 'Nothing waiting for you'} />
      <Space wrap style={{ marginBottom: 16 }}>
        <Segmented value={scope} options={options} onChange={(v) => { setSearch({ scope: v }); setPage(1); }} />
        <Select placeholder="Status" allowClear style={{ width: 150 }} value={status} onChange={(v) => { setStatus(v); setPage(1); }}
          options={Object.entries(STATUS).map(([value, s]) => ({ value, label: s.label }))} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={load} /></Tooltip>
      </Space>
      <Table rowKey="id" columns={columns} dataSource={rows} loading={loading} scroll={{ x: 800 }}
        locale={{ emptyText: scope === 'awaiting' ? 'Nothing waiting for your decision' : 'No requests' }}
        pagination={{ current: page, pageSize: 20, total, onChange: setPage, showSizeChanger: false }} />
    </>
  );
};

export default ApprovalIndex;
