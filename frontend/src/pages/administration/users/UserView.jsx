// src/pages/administration/users/UserView.jsx
//
// /administration/users/:id — one user, read only: account details, their
// roles & what those roles allow, and their history (Activity). Loads by the
// :id param, so a direct link or a refresh works. Edit opens the same dialog
// as the list; Delete goes back to the list.
// Reference view page: copy it for a new module's record view.

import { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { App, Avatar, Button, Col, Popconfirm, Row, Spin, Tag } from 'antd';
import { DeleteOutlined, EditOutlined } from '@ant-design/icons';
import ViewHeader from '../../../components/ViewHeader';
import ViewSection from '../../../components/ViewSection';
import AuditHistory from '../../../components/AuditHistory';
import Attachments from '../../../components/Attachments';
import ApprovalPanel from '../../../components/approvals/ApprovalPanel';
import RecordNotFound from '../../../components/RecordNotFound';
import useAuth from '../../../hooks/useAuth';
import useUserStore from '../../../store/userStore';
import userApi from '../../../services/administration/userApi';
import handleApiError from '../../../utils/handleApiError';
import { formatDateTime } from '../../../utils/formatDate';
import { plural } from '../../../utils/permissionGroups';
import { RolePermissionsPanel } from '../components/RolePermissionsModal';
import UserFormModal from './UserFormModal';

const USERS_PATH = '/administration/users';

const initials = (name = '') => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('') || '?';

const UserView = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { message } = App.useApp();
  const { user: me, can } = useAuth();
  const canEdit = can('user-edit');
  const canDelete = can('user-delete');
  const roles = useUserStore((state) => state.roles);
  const fetchFormOptions = useUserStore((state) => state.fetchFormOptions);
  const [user, setUser] = useState(null);
  const [state, setState] = useState('loading');   // loading | ready | missing
  const [editing, setEditing] = useState(false);

  const load = useCallback(async () => {
    try {
      const { data } = await userApi.getById(id);
      setUser(data.data);
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
      const { data } = await userApi.remove(user.id);
      message.success(data.message);
      navigate(USERS_PATH);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  if (state === 'loading') return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  if (state === 'missing') return <RecordNotFound title="User not found" backTo={USERS_PATH} />;

  const permissionCount = new Set(user.roles.flatMap((r) => r.permissions.map((p) => p.name))).size;

  return (
    <>
      <ViewHeader
        backTo={USERS_PATH}
        avatar={<Avatar size={48} className="app-avatar">{initials(user.name)}</Avatar>}
        title={user.name}
        tags={<>
          <Tag color={user.active ? 'green' : 'default'}>{user.active ? 'Active' : 'Inactive'}</Tag>
          {user.id === me?.id && <Tag color="cyan">You</Tag>}
        </>}
        subtitle={user.email}
        extra={<>
          {canEdit && <Button type="primary" icon={<EditOutlined />} onClick={() => setEditing(true)}>Edit</Button>}
          {canDelete && user.id !== me?.id && (
            <Popconfirm title="Delete this user?" description={user.email} okText="Delete" okButtonProps={{ danger: true }} onConfirm={remove}>
              <Button danger icon={<DeleteOutlined />}>Delete</Button>
            </Popconfirm>
          )}
        </>}
      />

      <Row gutter={[16, 16]}>
        <Col xs={24} xl={10}>
          <ViewSection title="Account" items={[
            ['Name', user.name],
            ['Email', user.email],
            ['Status', <Tag key="s" color={user.active ? 'green' : 'default'}>{user.active ? 'Active' : 'Inactive'}</Tag>],
            ['Roles', user.roles.length ? user.roles.map((r) => <Tag key={r.id} color="cyan">{r.name}</Tag>) : null],
            ['Password', user.must_change_password ? <Tag key="p" color="orange">Must change at next sign-in</Tag> : 'Set by the user'],
            ['Last sign-in', user.last_login_at && formatDateTime(user.last_login_at)],
            ['Created', formatDateTime(user.created_at)],
          ]} />
        </Col>
        <Col xs={24} xl={14}>
          <ViewSection title="Roles & Permissions" extra={<span style={{ color: 'var(--muted)' }}>{plural(user.roles.length, 'role')} · {plural(permissionCount, 'permission')}</span>}>
            <RolePermissionsPanel key={user.id} roles={user.roles} height={null} />
          </ViewSection>
        </Col>
        <Col xs={24} xl={12}>
          <Attachments type="user" recordId={user.id} />
        </Col>
        <Col xs={24} xl={12}>
          <ApprovalPanel type="user" recordId={user.id} />
        </Col>
        <Col xs={24}>
          <AuditHistory record="User" recordId={user.id} />
        </Col>
      </Row>

      <UserFormModal record={editing ? user : null} roles={roles} onClose={() => setEditing(false)}
        onSaved={() => { setEditing(false); load(); }} />
    </>
  );
};

export default UserView;
