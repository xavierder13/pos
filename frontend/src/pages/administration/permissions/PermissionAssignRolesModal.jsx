// src/pages/administration/permissions/PermissionAssignRolesModal.jsx
//
// Give one permission to many roles at once (Permissions → "Assign roles"):
// every role in a Transfer. Save replaces the permission's list: roles moved
// in get it, roles moved out lose it. The Administrator role always has every
// permission, so it's fixed on the right.

import { useEffect, useMemo, useState } from 'react';
import { App, Modal, Spin, Tag, Transfer, Typography } from 'antd';
import permissionApi from '../../../services/administration/permissionApi';
import handleApiError from '../../../utils/handleApiError';
import { plural } from '../../../utils/permissionGroups';

const { Text } = Typography;

const PermissionAssignRolesModal = ({ permission, onClose, onSaved }) => {
  const { message } = App.useApp();
  const before = useMemo(() => permission.roles.map((r) => r.id), [permission]);
  const [roles, setRoles] = useState(null);
  const [assigned, setAssigned] = useState(before);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        const { data } = await permissionApi.roleOptions();
        if (!cancelled) setRoles(data.roles);
      } catch (error) {
        if (!cancelled) { setRoles([]); handleApiError(error, message); }
      }
    };
    load();
    return () => { cancelled = true; };
  }, [message]);

  const items = useMemo(() => (roles ?? []).map((r) => ({ key: r.id, ...r, disabled: r.read_only })), [roles]);
  const added = assigned.filter((id) => !before.includes(id)).length;
  const removed = before.filter((id) => !assigned.includes(id)).length;

  const save = async () => {
    setSaving(true);
    try {
      const { data } = await permissionApi.assignRoles(permission.id, assigned);
      message.success(`${permission.name}: ${plural(data.data.roles.length, 'role')}${added ? ` · ${added} added` : ''}${removed ? ` · ${removed} removed` : ''}.`);
      onSaved(data.data);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal open keyboard={false} destroyOnHidden width={{ xs: '100%', sm: '95%', md: 760 }}
      title={<>Assign Roles — <Text code>{permission.name}</Text></>}
      okText="Update" onOk={save} confirmLoading={saving} okButtonProps={{ disabled: roles === null || (!added && !removed) }} onCancel={onClose}>
      <Spin spinning={roles === null}>
        <Text type="secondary" style={{ display: 'block', marginBottom: 8 }}>
          Move the roles that should have this permission to Assigned. Every user with those roles gets it.
        </Text>
        <Transfer
          dataSource={items}
          targetKeys={assigned}
          onChange={(keys) => setAssigned(keys.map(Number))}
          titles={['Roles', 'Assigned']}
          showSearch
          filterOption={(input, item) => item.name.toLowerCase().includes(input.trim().toLowerCase())}
          render={(item) => (
            <span>
              {item.name} <Text type="secondary">· {plural(item.users_count, 'user')}</Text>
              {item.read_only && <Tag style={{ marginLeft: 4 }}>always</Tag>}
            </span>
          )}
          styles={{ section: { width: 'calc(50% - 20px)', height: 380 } }}
          locale={{ notFoundContent: 'No roles', searchPlaceholder: 'Search role' }}
        />
        {(added > 0 || removed > 0) && <Text type="secondary" style={{ display: 'block', marginTop: 8 }}>Unsaved: {added} to add, {removed} to remove.</Text>}
      </Spin>
    </Modal>
  );
};

export default PermissionAssignRolesModal;
