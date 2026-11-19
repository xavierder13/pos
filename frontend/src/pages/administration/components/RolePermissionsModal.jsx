// src/pages/administration/components/RolePermissionsModal.jsx
//
// Roles & Permissions viewer: RolePermissionsPanel (embedded in the User /
// Role view pages) and RolePermissionsModal (the same panel in a dialog, from
// a list row). Shows `roles` (each { id, name, permissions:
// [{ id, name }] }): a user's roles from the Users list row, or one role from
// the Roles list. No fetch. Two views:
// - By role: each role expands to its permissions by module
// - By permission: each module expands to its permissions and the roles that grant them
// The search matches a role, module or permission and opens the rows it
// matches. Mount it with a `key` per subject so the search starts empty.

import { useMemo, useState } from 'react';
import { Button, Input, Modal, Segmented, Space, Table, Tag, Typography } from 'antd';
import { ColumnHeightOutlined, VerticalAlignMiddleOutlined } from '@ant-design/icons';
import ExpandIcon from '../../../components/ExpandIcon';
import { groupByModule, moduleLabel, plural, splitPermission } from '../../../utils/permissionGroups';

const { Text } = Typography;
const HIGHLIGHT = { background: 'var(--accent-soft)', color: 'var(--accent)', borderRadius: 4, padding: '0 2px' };

export const RolePermissionsPanel = ({ roles: rolesProp, height = '55vh' }) => {
  const roles = useMemo(() => rolesProp || [], [rolesProp]);
  const [search, setSearch] = useState('');
  const [view, setView] = useState('role');
  const [expanded, setExpanded] = useState(() => (roles.length === 1 ? { role: [roles[0].id], permission: [] } : { role: [], permission: [] }));

  // every permission once, with the roles that grant it
  const allPermissions = useMemo(() => {
    const byName = {};
    roles.forEach((role) => (role.permissions || []).forEach((p) => {
      (byName[p.name] ||= { id: p.id, name: p.name, roles: [] }).roles.push(role.name);
    }));
    return Object.values(byName);
  }, [roles]);
  const moduleCount = useMemo(() => groupByModule(allPermissions).length, [allPermissions]);

  const q = search.trim().toLowerCase();
  const hit = (text) => !!q && text.toLowerCase().includes(q);
  const permissionMatches = (p, v) => p.name.toLowerCase().includes(v) || moduleLabel(splitPermission(p.name).module).toLowerCase().includes(v);

  // By role: a role matching the search keeps all its permissions; otherwise only the ones that match
  const roleRows = useMemo(() => roles
    .map((role) => {
      const permissions = role.permissions || [];
      const shown = !q || role.name.toLowerCase().includes(q) ? permissions : permissions.filter((p) => permissionMatches(p, q));
      return { key: role.id, name: role.name, total: permissions.length, groups: groupByModule(shown), shown: shown.length };
    })
    .filter((row) => !q || row.name.toLowerCase().includes(q) || row.shown)
    .sort((a, b) => a.name.localeCompare(b.name)), [roles, q]);

  // By permission: one row per module; a module matching the search keeps all its permissions
  const moduleRows = useMemo(() => groupByModule(allPermissions)
    .map((g) => {
      const shown = !q || g.label.toLowerCase().includes(q)
        ? g.permissions
        : g.permissions.filter((p) => p.name.toLowerCase().includes(q) || p.roles.some((r) => r.toLowerCase().includes(q)));
      return { ...g, key: g.module, shown };
    })
    .filter((g) => g.shown.length), [allPermissions, q]);

  const rows = view === 'role' ? roleRows : moduleRows;
  const allKeys = rows.map((r) => r.key);
  const expandedKeys = expanded[view];
  const setExpandedKeys = (keys) => setExpanded((prev) => ({ ...prev, [view]: keys }));

  // a search opens every row it leaves (in both views); clearing it collapses them
  const onSearch = (value) => {
    setSearch(value);
    const v = value.trim().toLowerCase();
    if (!v) {
      setExpanded({ role: [], permission: [] });
      return;
    }
    setExpanded({
      role: roles.filter((r) => r.name.toLowerCase().includes(v) || (r.permissions || []).some((p) => permissionMatches(p, v))).map((r) => r.id),
      permission: groupByModule(allPermissions)
        .filter((g) => g.label.toLowerCase().includes(v) || g.permissions.some((p) => p.name.toLowerCase().includes(v) || p.roles.some((r) => r.toLowerCase().includes(v))))
        .map((g) => g.module),
    });
  };

  const actionTag = (p) => (
    <Tag key={p.id} color={hit(p.name) ? 'gold' : 'cyan'} title={p.name} style={{ marginBottom: 4 }}>{p.action || p.name}</Tag>
  );

  const roleColumns = [
    { title: 'Role', dataIndex: 'name', render: (name) => <Text strong style={hit(name) ? HIGHLIGHT : undefined}>{name}</Text> },
    { title: 'Modules', key: 'modules', width: 90, align: 'right', render: (_, row) => row.groups.length },
    {
      title: 'Permissions', key: 'permissions', width: 120, align: 'right',
      render: (_, row) => (q && row.shown !== row.total ? <Text>{row.shown} <Text type="secondary">of {row.total}</Text></Text> : row.total),
    },
  ];

  const moduleColumns = [
    { title: 'Module', dataIndex: 'label', width: '30%', render: (label) => <Text strong style={hit(label) ? HIGHLIGHT : undefined}>{label}</Text> },
    { title: 'Actions', key: 'actions', render: (_, row) => row.shown.map(actionTag) },
  ];

  const renderRole = (row) => (row.groups.length ? (
    <Table size="small" rowKey="module" pagination={false} dataSource={row.groups} columns={[
      { title: 'Module', dataIndex: 'label', width: '35%', render: (label) => <Text style={hit(label) ? HIGHLIGHT : undefined}>{label}</Text> },
      { title: 'Actions', key: 'actions', render: (_, g) => g.permissions.map(actionTag) },
    ]} />
  ) : <Text type="secondary">This role has no permissions.</Text>);

  const renderModule = (row) => (
    <Table size="small" rowKey="name" pagination={false} dataSource={row.shown} columns={[
      { title: 'Permission', dataIndex: 'name', width: '50%', render: (name) => <Text code style={hit(name) ? HIGHLIGHT : undefined}>{name}</Text> },
      { title: 'Granted by', dataIndex: 'roles', render: (granted) => granted.map((r) => <Tag key={r} color={hit(r) ? 'gold' : undefined} style={{ marginBottom: 4 }}>{r}</Tag>) },
    ]} />
  );

  return (
      <Space orientation="vertical" size={12} style={{ width: '100%' }}>
        <Text type="secondary">
          {plural(roles.length, 'role')} · {plural(allPermissions.length, 'unique permission')} · {plural(moduleCount, 'module')}
        </Text>
        <Space style={{ width: '100%', justifyContent: 'space-between' }} wrap>
          <Space wrap>
            <Segmented value={view} onChange={setView}
              options={[{ value: 'role', label: 'By role' }, { value: 'permission', label: 'By permission' }]} />
            <Button icon={<ColumnHeightOutlined />} onClick={() => setExpandedKeys(allKeys)}
              disabled={!rows.length || allKeys.every((k) => expandedKeys.includes(k))}>Expand all</Button>
            <Button icon={<VerticalAlignMiddleOutlined />} onClick={() => setExpandedKeys([])} disabled={!expandedKeys.length}>Collapse all</Button>
          </Space>
          <Input.Search placeholder="Search role, module or permission" allowClear value={search}
            onChange={(e) => onSearch(e.target.value)} style={{ width: 300, maxWidth: '100%' }} />
        </Space>
        <Table size="small" bordered rowKey="key" pagination={false} dataSource={rows} components={{ ExpandIcon }}
          columns={view === 'role' ? roleColumns : moduleColumns} scroll={height ? { y: height } : undefined}
          locale={{ emptyText: roles.length ? 'No role, module or permission matches the search' : 'No roles' }}
          expandable={{
            expandedRowKeys: expandedKeys,
            onExpandedRowsChange: (keys) => setExpandedKeys([...keys]),
            expandedRowRender: view === 'role' ? renderRole : renderModule,
            rowExpandable: (row) => (view === 'role' ? true : row.shown.length > 0),
              expandRowByClick: true,
          }} />
      </Space>
  );
};

const RolePermissionsModal = ({ open, title, roles, onClose }) => (
  <Modal keyboard={false} open={open} title={title} onCancel={onClose} footer={null} width={{ xs: '100%', sm: '95%', md: 900 }}>
    <RolePermissionsPanel roles={roles} />
  </Modal>
);

export default RolePermissionsModal;
