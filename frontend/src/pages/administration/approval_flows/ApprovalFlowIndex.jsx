// src/pages/administration/approval_flows/ApprovalFlowIndex.jsx
//
// Administration → Approval Flows (approval-flow-edit): which records need
// approval and who approves at each level. Stored in the database — no code
// change. One active flow per record type.

import { useCallback, useEffect, useState } from 'react';
import { App, Button, Popconfirm, Space, Table, Tag, Tooltip } from 'antd';
import ActionButton from '../../../components/ActionButton';
import { PlusOutlined, ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import approvalFlowApi from '../../../services/administration/approvalFlowApi';
import handleApiError from '../../../utils/handleApiError';
import ApprovalFlowFormModal from './ApprovalFlowFormModal';

const ApprovalFlowIndex = () => {
  const { message } = App.useApp();
  const [rows, setRows] = useState([]);
  const [options, setOptions] = useState({ record_types: [], users: [], roles: [] });
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [{ data: flows }, { data: opts }] = await Promise.all([approvalFlowApi.getAll(), approvalFlowApi.options()]);
      setRows(flows.data);
      setOptions(opts);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const remove = async (row) => {
    try {
      const { data } = await approvalFlowApi.remove(row.id);
      message.success(data.message);
      load();
    } catch (error) {
      handleApiError(error, message);
    }
  };

  const columns = [
    { title: 'Flow', dataIndex: 'name' },
    { title: 'Record', dataIndex: 'record_label', width: 140 },
    {
      title: 'Levels', key: 'levels',
      render: (_, r) => <Space size={4} wrap>{r.levels.map((l) => <Tag key={l.level} color="cyan">{l.level}. {l.approver}</Tag>)}</Space>,
    },
    { title: 'Status', dataIndex: 'active', width: 100, render: (a) => <Tag color={a ? 'green' : 'default'}>{a ? 'Active' : 'Inactive'}</Tag> },
    { title: 'Pending', dataIndex: 'pending_count', width: 90 },
    {
      title: 'Actions', key: 'actions', width: 100,
      render: (_, r) => (
        <Space size={6}>
          <ActionButton kind="edit" onClick={() => setEditing(r)} />
          <Popconfirm title="Delete this flow?" description={r.name} okText="Delete" okButtonProps={{ danger: true }} onConfirm={() => remove(r)}>
            <ActionButton kind="delete" />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  return (
    <>
      <PageHeader title="Approval Flows" subtitle="Who approves what — set up here, no code change." extra={<>
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={load} /></Tooltip>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setEditing({})}>Add Flow</Button>
      </>} />
      <Table rowKey="id" columns={columns} dataSource={rows} loading={loading} scroll={{ x: 800 }} pagination={false}
        locale={{ emptyText: 'No approval flows yet — add one to require approval for a kind of record.' }} />
      <ApprovalFlowFormModal record={editing} options={options} onClose={() => setEditing(null)} onSaved={() => { setEditing(null); load(); }} />
    </>
  );
};

export default ApprovalFlowIndex;
