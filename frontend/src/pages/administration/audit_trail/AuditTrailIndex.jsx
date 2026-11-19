// src/pages/administration/audit_trail/AuditTrailIndex.jsx
//
// Who created, changed or deleted what and when. Server-paged; a row
// expands to its field changes (old → new).

import { useCallback, useEffect, useState } from 'react';
import { App, Button, DatePicker, Input, Select, Space, Table, Tag, Tooltip, Typography } from 'antd';
import { ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import auditTrailApi from '../../../services/administration/auditTrailApi';
import handleApiError from '../../../utils/handleApiError';
import { formatDateTime } from '../../../utils/formatDate';
import ExpandIcon from '../../../components/ExpandIcon';

const ACTION_COLORS = { created: 'green', updated: 'blue', deleted: 'red' };
const DEFAULT_PARAMS = { page: 1, per_page: 20 };

const show = (value) => {
  if (value === null || value === undefined || value === '') return <Typography.Text type="secondary">—</Typography.Text>;
  return typeof value === 'object' ? JSON.stringify(value) : String(value);
};

const AuditTrailIndex = () => {
  const { message } = App.useApp();
  const [params, setParams] = useState(DEFAULT_PARAMS);
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);
  const [options, setOptions] = useState({ modules: [], records: [], users: [] });

  const load = useCallback(async (next) => {
    setLoading(true);
    try {
      const { data } = await auditTrailApi.getAll(next);
      setRows(data.data);
      setTotal(data.meta.total);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [message]);

  useEffect(() => {
    const run = async () => { await load(params); };
    run();
  }, [load, params]);

  useEffect(() => {
    auditTrailApi.options().then(({ data }) => setOptions(data.data)).catch(() => {});
  }, []);

  const change = (changes) => setParams((p) => ({ ...p, page: 1, ...changes }));

  const columns = [
    { title: 'When', dataIndex: 'created_at', key: 'created_at', width: 190, render: formatDateTime },
    { title: 'User', dataIndex: 'user', key: 'user', width: 180, render: show },
    { title: 'Action', dataIndex: 'action', key: 'action', width: 110, render: (a) => <Tag color={ACTION_COLORS[a]}>{a}</Tag> },
    { title: 'Record', key: 'record', width: 160, render: (_, r) => `${r.record ?? ''} #${r.record_id ?? ''}` },
    { title: 'Module', dataIndex: 'module', key: 'module', width: 150 },
    { title: 'Changes', key: 'changes', render: (_, r) => r.changes.map((c) => c.field).join(', ') || '—' },
  ];

  const expandedRowRender = (record) => (
    <Table size="small" rowKey="field" pagination={false} dataSource={record.changes} columns={[
      { title: 'Field', dataIndex: 'field', width: 200 },
      { title: 'Before', dataIndex: 'old', render: show },
      { title: 'After', dataIndex: 'new', render: show },
    ]} />
  );

  return (
    <>
      <PageHeader title="Audit Trail" />
      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Search" allowClear style={{ width: 200 }} onSearch={(search) => change({ search })} />
        <Select placeholder="User" allowClear showSearch optionFilterProp="label" style={{ width: 180 }}
          options={options.users.map((u) => ({ value: u.id, label: u.name }))} onChange={(causer_id) => change({ causer_id })} />
        <Select placeholder="Record" allowClear style={{ width: 140 }}
          options={options.records.map((r) => ({ value: r, label: r }))} onChange={(record) => change({ record })} />
        <Select placeholder="Action" allowClear style={{ width: 130 }}
          options={['created', 'updated', 'deleted'].map((a) => ({ value: a, label: a }))} onChange={(action) => change({ action })} />
        <DatePicker.RangePicker onChange={(dates) => change({
          date_from: dates?.[0]?.format('YYYY-MM-DD'), date_to: dates?.[1]?.format('YYYY-MM-DD'),
        })} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => load(params)} /></Tooltip>
      </Space>
      <Table rowKey="id" columns={columns} dataSource={rows} loading={loading} scroll={{ x: 900 }} components={{ ExpandIcon }}
        expandable={{ expandedRowRender, rowExpandable: (r) => r.changes.length > 0 }}
        onChange={(p) => setParams((prev) => ({ ...prev, page: p.current, per_page: p.pageSize }))}
        pagination={{ current: params.page, pageSize: params.per_page, total, showTotal: (n) => `${n} entr${n === 1 ? 'y' : 'ies'}` }} />
    </>
  );
};

export default AuditTrailIndex;
