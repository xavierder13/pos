// src/pages/administration/permissions/PermissionRolesModal.jsx
//
// Which roles have one permission (Permissions → View roles): each role and
// how many users hold it. From the /permissions row; no fetch. Mount with a
// `key` per permission so the search starts empty.

import { useMemo, useState } from 'react';
import { Input, Modal, Space, Table, Tag, Typography } from 'antd';
import { ADMINISTRATOR } from '../../../hooks/useAuth';
import { plural } from '../../../utils/permissionGroups';

const PermissionRolesModal = ({ open, permission, onClose }) => {
  const [search, setSearch] = useState('');
  const roles = useMemo(() => permission?.roles || [], [permission]);
  const shown = roles.filter((r) => !search || r.name.toLowerCase().includes(search.toLowerCase()));
  const users = roles.reduce((n, r) => n + (r.users_count || 0), 0);

  return (
    <Modal keyboard={false} open={open} title={<>Roles with <Typography.Text code>{permission?.name}</Typography.Text></>}
      onCancel={onClose} footer={null} width={{ xs: '100%', sm: '95%', md: 640 }}>
      <Space orientation="vertical" size={12} style={{ width: '100%' }}>
        <Space style={{ width: '100%', justifyContent: 'space-between' }} wrap>
          <Typography.Text type="secondary">{`${plural(roles.length, 'role')} · ${plural(users, 'user assignment')} through them`}</Typography.Text>
          <Input.Search allowClear placeholder="Search role" onChange={(e) => setSearch(e.target.value)} style={{ width: 220, maxWidth: '100%' }} />
        </Space>
        <Table rowKey="id" size="small" bordered dataSource={shown} pagination={false} scroll={{ y: '50vh' }}
          locale={{ emptyText: roles.length ? 'No role matches the search' : 'No role has this permission' }}
          columns={[
            {
              title: 'Role', dataIndex: 'name',
              render: (name) => <Space><Typography.Text strong>{name}</Typography.Text>{name === ADMINISTRATOR && <Tag>Read-only</Tag>}</Space>,
            },
            { title: 'Users', dataIndex: 'users_count', width: 90, align: 'right', render: (v) => v || <Typography.Text type="secondary">0</Typography.Text> },
          ]} />
      </Space>
    </Modal>
  );
};

export default PermissionRolesModal;
