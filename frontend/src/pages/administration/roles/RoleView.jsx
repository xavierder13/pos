// src/pages/administration/roles/RoleView.jsx
//
// /administration/roles/:id — one role, read only: what it allows (by
// module), who has it, and its history. Actions: Assign users, Edit, Delete
// (the Administrator role: Assign users only).

import { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { App, Button, Col, Input, Popconfirm, Row, Spin, Table, Tag } from 'antd';
import ActionButton from '../../../components/ActionButton';
import { DeleteOutlined, EditOutlined, LockOutlined, UsergroupAddOutlined } from '@ant-design/icons';
import ViewHeader from '../../../components/ViewHeader';
import ViewSection from '../../../components/ViewSection';
import AuditHistory from '../../../components/AuditHistory';
import RecordNotFound from '../../../components/RecordNotFound';
import useAuth from '../../../hooks/useAuth';
import useRoleStore from '../../../store/roleStore';
import roleApi from '../../../services/administration/roleApi';
import handleApiError from '../../../utils/handleApiError';
import { formatDateTime } from '../../../utils/formatDate';
import { groupByModule, plural } from '../../../utils/permissionGroups';
import { RolePermissionsPanel } from '../components/RolePermissionsModal';
import RoleFormModal from './RoleFormModal';
import RoleAssignUsersModal from './RoleAssignUsersModal';

const ROLES_PATH = '/administration/roles';

const RoleView = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { message } = App.useApp();
  const { can } = useAuth();
  const canEdit = can('role-edit');
  const canDelete = can('role-delete');
  const canOpenUsers = can('user-list');
  const permissions = useRoleStore((state) => state.permissions);
  const fetchFormOptions = useRoleStore((state) => state.fetchFormOptions);
  const [role, setRole] = useState(null);
  const [state, setState] = useState('loading');
  const [editing, setEditing] = useState(false);
  const [assigning, setAssigning] = useState(false);
  const [search, setSearch] = useState('');

  const load = useCallback(async () => {
    try {
      const { data } = await roleApi.getById(id);
      setRole(data.data);
      setState('ready');
    } catch (error) {
      if (error.response?.status === 404) setState('missing');
      else { handleApiError(error, message); setState((s) => (s === 'loading' ? 'missing' : s)); }
    }
  }, [id, message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  useEffect(() => {
    if (canEdit) fetchFormOptions();
  }, [canEdit, fetchFormOptions]);

  const remove = async () => {
    try {
      const { data } = await roleApi.remove(role.id);
      message.success(data.message);
      navigate(ROLES_PATH);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  if (state === 'loading') return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  if (state === 'missing') return <RecordNotFound title="Role not found" backTo={ROLES_PATH} />;

  const term = search.trim().toLowerCase();
  const users = (role.users ?? []).filter((u) => !term || u.name.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));

  return (
    <>
      <ViewHeader
        backTo={ROLES_PATH}
        title={role.name}
        tags={role.read_only && <Tag icon={<LockOutlined />} color="gold">Every permission · read only</Tag>}
        subtitle={`${plural(role.permissions.length, 'permission')} in ${plural(groupByModule(role.permissions).length, 'module')} · ${plural(role.users_count, 'user')}`}
        extra={<>
          {canEdit && <Button icon={<UsergroupAddOutlined />} onClick={() => setAssigning(true)}>Assign users</Button>}
          {canEdit && !role.read_only && <Button type="primary" icon={<EditOutlined />} onClick={() => setEditing(true)}>Edit</Button>}
          {canDelete && !role.read_only && (
            <Popconfirm title="Delete this role?" description={role.name} okText="Delete" okButtonProps={{ danger: true }} onConfirm={remove}>
              <Button danger icon={<DeleteOutlined />}>Delete</Button>
            </Popconfirm>
          )}
        </>}
      />

      <Row gutter={[16, 16]}>
        <Col xs={24} xl={14}>
          <ViewSection title="Permissions">
            <RolePermissionsPanel key={`${role.id}-${role.permissions.length}`} roles={[role]} height={null} />
          </ViewSection>
        </Col>
        <Col xs={24} xl={10}>
          <ViewSection title="Users with this role" extra={<Input.Search size="small" allowClear placeholder="Search" style={{ width: 170 }} onChange={(e) => setSearch(e.target.value)} />}>
            <Table rowKey="id" size="small" dataSource={users} pagination={{ pageSize: 10, hideOnSinglePage: true, showSizeChanger: false }}
              locale={{ emptyText: role.users?.length ? 'No user matches the search' : 'No user has this role yet' }}
              columns={[
                { title: 'Name', dataIndex: 'name', sorter: (a, b) => a.name.localeCompare(b.name) },
                { title: 'Email', dataIndex: 'email', ellipsis: true },
                { title: 'Status', dataIndex: 'active', width: 90, render: (a) => <Tag color={a ? 'green' : 'default'}>{a ? 'Active' : 'Inactive'}</Tag> },
                ...(canOpenUsers ? [{
                  title: '', key: 'actions', width: 48,
                  render: (_, u) => <ActionButton kind="view" title="View user" onClick={() => navigate(`/administration/users/${u.id}`)} />,
                }] : []),
              ]} />
          </ViewSection>
          <div style={{ height: 16 }} />
          <ViewSection title="Details" items={[
            ['Name', role.name],
            ['Created', formatDateTime(role.created_at)],
          ]} />
        </Col>
        <Col xs={24}>
          <AuditHistory record="Role" recordId={role.id} />
        </Col>
      </Row>

      <RoleFormModal record={editing ? role : null} permissions={permissions} onClose={() => setEditing(false)}
        onSaved={() => { setEditing(false); load(); }} />
      {assigning && (
        <RoleAssignUsersModal role={role} onClose={() => setAssigning(false)} onSaved={() => { setAssigning(false); load(); }} />
      )}
    </>
  );
};

export default RoleView;
