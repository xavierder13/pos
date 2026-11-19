// src/pages/administration/roles/RoleAssignUsersModal.jsx
//
// Assign one role to many users at once (Roles → "Assign users"): every
// account in a Transfer, searchable by name / email / current roles, the
// left list limited to active accounts unless switched off. Save replaces
// the role's list: users moved in get the role, users moved out lose it
// (their other roles stay). The Administrator role always keeps one active user.

import { useEffect, useMemo, useState } from 'react';
import { App, Modal, Space, Spin, Switch, Tag, Transfer, Typography } from 'antd';
import roleApi from '../../../services/administration/roleApi';
import handleApiError from '../../../utils/handleApiError';
import { plural } from '../../../utils/permissionGroups';

const { Text } = Typography;

const RoleAssignUsersModal = ({ role, onClose, onSaved }) => {
  const { message } = App.useApp();
  const [users, setUsers] = useState(null);
  const [assigned, setAssigned] = useState([]);
  const [before, setBefore] = useState([]);
  const [activeOnly, setActiveOnly] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        const { data } = await roleApi.users(role.id);
        if (cancelled) return;
        setUsers(data.users);
        setAssigned(data.data);
        setBefore(data.data);
      } catch (error) {
        if (!cancelled) { setUsers([]); handleApiError(error, message); }
      }
    };
    load();
    return () => { cancelled = true; };
  }, [role.id, message]);

  // inactive accounts hide unless asked for; an assigned one always shows (it can be moved out)
  const items = useMemo(() => (users ?? [])
    .filter((u) => !activeOnly || u.active || assigned.includes(u.id))
    .map((u) => ({ key: u.id, ...u, search: `${u.name} ${u.email} ${u.roles.join(' ')}`.toLowerCase() })), [users, activeOnly, assigned]);
  const added = assigned.filter((id) => !before.includes(id)).length;
  const removed = before.filter((id) => !assigned.includes(id)).length;

  const save = async () => {
    setSaving(true);
    try {
      await roleApi.assignUsers(role.id, assigned);
      message.success(`${role.name}: ${plural(assigned.length, 'user')}${added ? ` · ${added} added` : ''}${removed ? ` · ${removed} removed` : ''}.`);
      onSaved();
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  const otherRoles = (item) => item.roles.filter((r) => r !== role.name);

  return (
    <Modal open keyboard={false} destroyOnHidden width={{ xs: '100%', sm: '95%', md: 920 }} title={`Assign Users — ${role.name}`}
      okText="Update" onOk={save} confirmLoading={saving} okButtonProps={{ disabled: users === null || (!added && !removed) }} onCancel={onClose}>
      <Spin spinning={users === null}>
        <Space style={{ width: '100%', justifyContent: 'space-between', marginBottom: 8 }} wrap>
          <Text type="secondary">Move the users who should have this role to Assigned. Their other roles stay.</Text>
          <Space size={6}>
            <Text type="secondary">Active accounts only</Text>
            <Switch size="small" checked={activeOnly} onChange={setActiveOnly} aria-label="Active accounts only" />
          </Space>
        </Space>
        <Transfer
          dataSource={items}
          targetKeys={assigned}
          onChange={(keys) => setAssigned(keys.map(Number))}
          titles={['Users', 'Assigned']}
          showSearch
          filterOption={(input, item) => item.search.includes(input.trim().toLowerCase())}
          render={(item) => (
            <span>
              {item.name} <Text type="secondary">{item.email}</Text>
              {!item.active && <Tag style={{ marginLeft: 4 }}>inactive</Tag>}
              {otherRoles(item).slice(0, 2).map((r) => <Tag key={r} color="cyan" style={{ marginLeft: 4 }}>{r}</Tag>)}
              {otherRoles(item).length > 2 && <Text type="secondary"> +{otherRoles(item).length - 2}</Text>}
            </span>
          )}
          styles={{ section: { width: 'calc(50% - 20px)', height: 420 } }}
          locale={{ notFoundContent: 'No users', searchPlaceholder: 'Search name, email or role' }}
          pagination
        />
        {(added > 0 || removed > 0) && <Text type="secondary" style={{ display: 'block', marginTop: 8 }}>Unsaved: {added} to add, {removed} to remove.</Text>}
      </Spin>
    </Modal>
  );
};

export default RoleAssignUsersModal;
