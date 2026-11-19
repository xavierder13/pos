// src/pages/administration/users/UserIndex.jsx
//
// Reference page for a server-paged list: filters + search, header sort,
// paging, add / edit dialog, delete with confirm — all through the store.

import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { App, Button, Input, Popconfirm, Select, Space, Table, Tag, Tooltip } from 'antd';
import ActionButton from '../../../components/ActionButton';
import { DownloadOutlined, PlusOutlined, ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../../components/PageHeader';
import useAuth from '../../../hooks/useAuth';
import useUsers from '../../../hooks/useUsers';
import userApi from '../../../services/administration/userApi';
import handleApiError from '../../../utils/handleApiError';
import downloadFile from '../../../utils/downloadFile';
import { formatDateTime } from '../../../utils/formatDate';
import UserFormModal from './UserFormModal';
import RolePermissionsModal from '../components/RolePermissionsModal';

const SORT_ORDER = { asc: 'ascend', desc: 'descend' };

const UserIndex = () => {
  const { message } = App.useApp();
  const navigate = useNavigate();
  const [exporting, setExporting] = useState(false);
  const { user: me, can } = useAuth();
  const canCreate = can('user-create');
  const canEdit = can('user-edit');
  const canDelete = can('user-delete');
  const { items, total, params, isLoading, roles, fetchItems } = useUsers({ withOptions: canCreate || canEdit });
  // the dialog: null = closed, {} = add, a user = edit
  const [editing, setEditing] = useState(null);
  // the user whose roles & permissions are shown
  const [viewingRoles, setViewingRoles] = useState(null);

  const remove = async (record) => {
    try {
      const { data } = await userApi.remove(record.id);
      message.success(data.message);
      fetchItems(items.length === 1 && params.page > 1 ? { page: params.page - 1 } : {});
    } catch (error) {
      handleApiError(error, message);
    }
  };

  // the whole filtered list as CSV (same filters / order as the table)
  const exportCsv = async () => {
    setExporting(true);
    try {
      const { search, active, role_id, sort, order } = params;
      const response = await userApi.export({ search, active, role_id, sort, order });
      await downloadFile(response, 'users.csv', message);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setExporting(false);
    }
  };

  const sortOrder = (key) => (params.sort === key ? SORT_ORDER[params.order] : null);

  const columns = [
    {
      title: 'Name', dataIndex: 'name', key: 'name', sorter: true, sortOrder: sortOrder('name'),
      render: (name, record) => <Link to={`/administration/users/${record.id}`}>{name}</Link>,
    },
    { title: 'Email', dataIndex: 'email', key: 'email', sorter: true, sortOrder: sortOrder('email') },
    {
      title: 'Roles', dataIndex: 'roles', key: 'roles',
      render: (list, record) => (list?.length ? (
        <Tooltip title="View roles & permissions">
          <span style={{ cursor: 'pointer' }} onClick={() => setViewingRoles(record)}>
            {list.map((r) => <Tag key={r.id} color="cyan">{r.name}</Tag>)}
          </span>
        </Tooltip>
      ) : '—'),
    },
    {
      title: 'Status', dataIndex: 'active', key: 'active', width: 110, sorter: true, sortOrder: sortOrder('active'),
      render: (active) => <Tag color={active ? 'green' : 'default'}>{active ? 'Active' : 'Inactive'}</Tag>,
    },
    {
      title: 'Last sign-in', dataIndex: 'last_login_at', key: 'last_login_at', width: 190,
      sorter: true, sortOrder: sortOrder('last_login_at'), render: formatDateTime,
    },
    {
      title: 'Actions', key: 'actions', width: 130,
      render: (_, record) => (
        <Space size={6}>
          <ActionButton kind="view" onClick={() => navigate(`/administration/users/${record.id}`)} />
          {canEdit && (
            <ActionButton kind="edit" onClick={() => setEditing(record)} />
          )}
          {canDelete && record.id !== me?.id && (
            <Popconfirm title="Delete this user?" description={record.email} okText="Delete" okButtonProps={{ danger: true }}
              onConfirm={() => remove(record)}>
              <ActionButton kind="delete" />
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ];

  const onTableChange = (pagination, _filters, sorter) => {
    const order = sorter?.order ? (sorter.order === 'ascend' ? 'asc' : 'desc') : undefined;
    fetchItems({
      page: pagination.current,
      per_page: pagination.pageSize,
      sort: order ? sorter.columnKey : undefined,
      order,
    });
  };

  return (
    <>
      <PageHeader title="Users" extra={canCreate && (
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setEditing({})}>Add User</Button>
      )} />

      <Space wrap style={{ marginBottom: 16 }}>
        <Input.Search placeholder="Search name or email" allowClear style={{ width: 260 }}
          defaultValue={params.search} onSearch={(search) => fetchItems({ search, page: 1 })} />
        <Select placeholder="Status" allowClear style={{ width: 140 }} value={params.active}
          options={[{ value: 1, label: 'Active' }, { value: 0, label: 'Inactive' }]}
          onChange={(active) => fetchItems({ active, page: 1 })} />
        {roles.length > 0 && (
          <Select placeholder="Role" allowClear style={{ width: 200 }} value={params.role_id}
            options={roles.map((r) => ({ value: r.id, label: r.name }))}
            onChange={(role_id) => fetchItems({ role_id, page: 1 })} />
        )}
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={() => fetchItems()} /></Tooltip>
        <Button icon={<DownloadOutlined />} loading={exporting} onClick={exportCsv}>Export CSV</Button>
      </Space>

      <Table rowKey="id" columns={columns} dataSource={items} loading={isLoading} onChange={onTableChange}
        scroll={{ x: 800 }}
        pagination={{ current: params.page, pageSize: params.per_page, total, showTotal: (n) => `${n} user${n === 1 ? '' : 's'}` }} />

      <RolePermissionsModal key={viewingRoles?.id ?? 'none'} open={!!viewingRoles}
        title={`Roles & Permissions — ${viewingRoles?.name ?? ''}`} roles={viewingRoles?.roles ?? []}
        onClose={() => setViewingRoles(null)} />

      <UserFormModal record={editing} roles={roles} onClose={() => setEditing(null)}
        onSaved={() => { setEditing(null); fetchItems(); }} />
    </>
  );
};

export default UserIndex;
