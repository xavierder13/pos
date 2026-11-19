// src/pages/administration/permissions/PermissionIndex.jsx
//
// Every permission (created by the backend's seeders) and the roles that
// have it. Row actions: View roles · Assign roles (role-edit).

import { useEffect, useMemo, useState } from 'react';
import { App, Button, Input, Select, Space, Table, Tag, Tooltip, Typography } from 'antd';
import ActionButton from '../../../components/ActionButton';
import { ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import useAuth from '../../../hooks/useAuth';
import permissionApi from '../../../services/administration/permissionApi';
import handleApiError from '../../../utils/handleApiError';
import { moduleLabel, plural, splitPermission } from '../../../utils/permissionGroups';
import PermissionRolesModal from './PermissionRolesModal';
import PermissionAssignRolesModal from './PermissionAssignRolesModal';

const PermissionIndex = () => {
  const { message } = App.useApp();
  const { can } = useAuth();
  const canAssign = can('role-edit');
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [module, setModule] = useState();
  const [viewing, setViewing] = useState(null);
  const [assigning, setAssigning] = useState(null);

  const load = async () => {
    setLoading(true);
    try {
      const { data } = await permissionApi.getAll();
      setItems(data.data.map((p) => ({ ...p, ...splitPermission(p.name) })));
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const run = async () => { await load(); };
    run();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const modules = useMemo(() => [...new Set(items.map((p) => p.module))].sort(), [items]);
  const term = search.trim().toLowerCase();
  const shown = items.filter((p) => (!module || p.module === module)
    && (!term || p.name.includes(term) || p.roles.some((r) => r.name.toLowerCase().includes(term))));

  const columns = [
    { title: 'Permission', dataIndex: 'name', key: 'name', sorter: (a, b) => a.name.localeCompare(b.name), render: (n) => <Typography.Text code>{n}</Typography.Text> },
    { title: 'Module', dataIndex: 'module', key: 'module', width: 170, sorter: (a, b) => a.module.localeCompare(b.module), render: moduleLabel },
    { title: 'Action', dataIndex: 'action', key: 'action', width: 120 },
    {
      title: 'Roles', dataIndex: 'roles', key: 'roles', width: 140, sorter: (a, b) => a.roles.length - b.roles.length,
      render: (roles, record) => <Tag color="cyan" style={{ cursor: 'pointer' }} onClick={() => setViewing(record)}>{plural(roles.length, 'role')}</Tag>,
    },
    {
      title: 'Actions', key: 'actions', width: 110,
      render: (_, record) => (
        <Space size={6}>
          <ActionButton kind="view" title="View roles" onClick={() => setViewing(record)} />
          {canAssign && (
            <ActionButton kind="assign" title="Assign roles" onClick={() => setAssigning(record)} />
          )}
        </Space>
      ),
    },
  ];

  return (
    <>
      <PageHeader title="Permissions" subtitle="Created by the backend's permission seeders; choose which roles have each one." />
      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Search permission or role" allowClear style={{ width: 260 }} onChange={(e) => setSearch(e.target.value)} />
        <Select placeholder="Module" allowClear style={{ width: 180 }} value={module} onChange={setModule}
          options={modules.map((m) => ({ value: m, label: moduleLabel(m) }))} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={load} /></Tooltip>
      </Space>
      <Table rowKey="id" columns={columns} dataSource={shown} loading={loading} pagination={{ pageSize: 50 }} scroll={{ x: 700 }} />

      <PermissionRolesModal key={viewing?.id ?? 'none'} open={!!viewing} permission={viewing} onClose={() => setViewing(null)} />
      {assigning && (
        <PermissionAssignRolesModal permission={assigning} onClose={() => setAssigning(null)}
          onSaved={(row) => {
            setAssigning(null);
            setItems((list) => list.map((p) => (p.id === row.id ? { ...row, ...splitPermission(row.name) } : p)));
          }} />
      )}
    </>
  );
};

export default PermissionIndex;
