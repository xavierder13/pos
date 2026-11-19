// src/components/AuditHistory.jsx
//
// One record's history from the Audit Trail (who changed what, when) — the
// "Activity" section of a view page. Server-paged; each entry expands to its
// field changes (before → after).
//   <AuditHistory record="User" recordId={user.id} />
// `record` is the name in the backend's AuditTrailController::RECORDS.
// Shown only to users with audit-trail-list (the Administrator always).

import { useCallback, useEffect, useState } from 'react';
import { App, Button, Table, Tag, Tooltip, Typography } from 'antd';
import { ReloadOutlined } from '@ant-design/icons';
import auditTrailApi from '../services/administration/auditTrailApi';
import useAuth from '../hooks/useAuth';
import handleApiError from '../utils/handleApiError';
import { formatDateTime } from '../utils/formatDate';
import ExpandIcon from './ExpandIcon';
import ViewSection from './ViewSection';

const ACTION_COLORS = { created: 'green', updated: 'cyan', deleted: 'red' };

const show = (value) => {
  if (value === null || value === undefined || value === '') return <Typography.Text type="secondary">—</Typography.Text>;
  if (Array.isArray(value)) return value.length ? value.join(', ') : <Typography.Text type="secondary">none</Typography.Text>;
  return typeof value === 'object' ? JSON.stringify(value) : String(value);
};

const AuditHistory = ({ record, recordId, title = 'Activity', pageSize = 10 }) => {
  const { message } = App.useApp();
  const { can } = useAuth();
  const allowed = can('audit-trail-list');
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);

  const load = useCallback(async (p) => {
    setLoading(true);
    try {
      const { data } = await auditTrailApi.getAll({ record, record_id: recordId, page: p, per_page: pageSize });
      setRows(data.data);
      setTotal(data.meta.total);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [record, recordId, pageSize, message]);

  useEffect(() => {
    if (!allowed) return;
    const run = async () => { await load(page); };
    run();
  }, [allowed, load, page]);

  if (!allowed) return null;

  return (
    <ViewSection title={title} extra={<Tooltip title="Reload"><Button size="small" icon={<ReloadOutlined />} onClick={() => load(page)} /></Tooltip>}>
      <Table rowKey="id" size="small" loading={loading} dataSource={rows} components={{ ExpandIcon }}
        locale={{ emptyText: 'No changes recorded yet' }}
        pagination={{ current: page, pageSize, total, onChange: setPage, showSizeChanger: false, hideOnSinglePage: true }}
        columns={[
          { title: 'When', dataIndex: 'created_at', width: 190, render: formatDateTime },
          { title: 'By', dataIndex: 'user', width: 180, render: show },
          { title: 'Action', dataIndex: 'action', width: 110, render: (a) => <Tag color={ACTION_COLORS[a]}>{a}</Tag> },
          { title: 'Fields', key: 'fields', render: (_, r) => r.changes.map((c) => c.field).join(', ') || '—' },
        ]}
        expandable={{
          rowExpandable: (r) => r.changes.length > 0,
          expandedRowRender: (r) => (
            <Table size="small" rowKey="field" pagination={false} dataSource={r.changes} columns={[
              { title: 'Field', dataIndex: 'field', width: 180 },
              { title: 'Before', dataIndex: 'old', render: show },
              { title: 'After', dataIndex: 'new', render: show },
            ]} />
          ),
        }} />
    </ViewSection>
  );
};

export default AuditHistory;
