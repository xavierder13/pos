// src/components/PageHeader.jsx
//
// A page's title row: title (+ optional subtitle) on the left, actions on the right.
//   <PageHeader title="Users" extra={<Button type="primary">Add User</Button>} />

import { Flex, Typography } from 'antd';

const PageHeader = ({ title, subtitle, extra }) => (
  <Flex justify="space-between" align="center" wrap gap={12} style={{ marginBottom: 16 }}>
    <div>
      <Typography.Title level={4} style={{ margin: 0 }}>{title}</Typography.Title>
      {subtitle && <Typography.Text type="secondary">{subtitle}</Typography.Text>}
    </div>
    {extra && <Flex gap={8} wrap>{extra}</Flex>}
  </Flex>
);

export default PageHeader;
