// src/pages/administration/roles/RoleIndex.jsx
//
// Reference page for a small, client-side list (the whole list at once).
// Row actions: View permissions · Assign users · Edit · Delete.

import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { App, Button, Input, Popconfirm, Space, Table, Tag, Tooltip } from 'antd';
import ActionButton from '../../../components/ActionButton';
import { LockOutlined, PlusOutlined, ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import useAuth from '../../../hooks/useAuth';
import useRoles from '../../../hooks/useRoles';
import roleApi from '../../../services/administration/roleApi';
import handleApiError from '../../../utils/handleApiError';
import { plural } from '../../../utils/permissionGroups';
import RoleFormModal from './RoleFormModal';
import RoleAssignUsersModal from './RoleAssignUsersModal';
import RolePermissionsModal from '../components/RolePermissionsModal';

const RoleIndex = () => {
  const { message } = App.useApp();
  const navigate = useNavigate();
  const { can } = useAuth();
  const canCreate = can('role-create');
  const canEdit = can('role-edit');
  const canDelete = can('role-delete');
  const { items, permissions, isLoading, fetchItems } = useRoles({ withOptions: canCreate || canEdit });
  const [editing, setEditing] = useState(null);      // null = closed, {} = add, a role = edit
  const [viewing, setViewing] = useState(null);      // the role whose permissions are shown
  const [assigning, setAssigning] = useState(null);  // the role whose users are being assigned
  const [search, setSearch] = useState('');

  const remove = async (record) => {
    try {
      const { data } = await roleApi.remove(record.id);
      message.success(data.message);
      fetchItems();
    } catch (error) {
      handleApiError(error, message);
    }
  };

  const shown = items.filter((r) => r.name.toLowerCase().includes(search.trim().toLowerCase()));

  const columns = [
    {
      title: 'Role', dataIndex: 'name', key: 'name', sorter: (a, b) => a.name.localeCompare(b.name),
      render: (name, record) => (
        <Space>
          <Link to={`/administration/roles/${record.id}`}>{name}</Link>
          {record.read_only && <Tooltip title="Has every permission; can't be changed"><LockOutlined style={{ color: 'var(--muted)' }} /></Tooltip>}
        </Space>
      ),
    },
    {
      title: 'Permissions', dataIndex: 'permissions', key: 'permissions', width: 160,
      sorter: (a, b) => (a.permissions?.length ?? 0) - (b.permissions?.length ?? 0),
      render: (list, record) => (
        <Tag color={record.read_only ? 'gold' : 'cyan'} style={{ cursor: 'pointer' }} onClick={() => setViewing(record)}>
          {record.read_only ? 'All' : plural(list?.length ?? 0, 'permission')}
        </Tag>
      ),
    },
    { title: 'Users', dataIndex: 'users_count', key: 'users_count', width: 100, sorter: (a, b) => a.users_count - b.users_count },
    {
      title: 'Actions', key: 'actions', width: 170,
      render: (_, record) => (
        <Space size={6}>
          <ActionButton kind="view" onClick={() => navigate(`/administration/roles/${record.id}`)} />
          {canEdit && (
            <ActionButton kind="assign" title="Assign users" onClick={() => setAssigning(record)} />
          )}
          {canEdit && !record.read_only && (
            <ActionButton kind="edit" onClick={() => setEditing(record)} />
          )}
          {canDelete && !record.read_only && (
            <Popconfirm title="Delete this role?" description={record.name} okText="Delete" okButtonProps={{ danger: true }}
              onConfirm={() => remove(record)}>
              <ActionButton kind="delete" />
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ];

  return (
    <>
      <PageHeader title="Roles" subtitle="What each role may do, and who has it." extra={canCreate && (
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setEditing({})}>Add Role</Button>
      )} />
      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Search roles" allowClear style={{ width: 260 }} onChange={(e) => setSearch(e.target.value)} />
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => fetchItems()} /></Tooltip>
      </Space>
      <Table rowKey="id" columns={columns} dataSource={shown} loading={isLoading} pagination={{ pageSize: 20 }} scroll={{ x: 640 }} />

      <RoleFormModal record={editing} permissions={permissions} onClose={() => setEditing(null)}
        onSaved={() => { setEditing(null); fetchItems(); }} />
      {assigning && (
        <RoleAssignUsersModal role={assigning} onClose={() => setAssigning(null)}
          onSaved={() => { setAssigning(null); fetchItems(); }} />
      )}
      <RolePermissionsModal key={viewing?.id ?? 'none'} open={!!viewing} title={`Permissions — ${viewing?.name ?? ''}`}
        roles={viewing ? [viewing] : []} onClose={() => setViewing(null)} />
    </>
  );
};

export default RoleIndex;
