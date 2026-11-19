// src/components/ViewHeader.jsx
//
// The top of a record's view page: Back to the list, the record's title with
// its status tags and a subtitle, and the page's actions on the right.
//   <ViewHeader backTo="/administration/users" title={user.name} tags={…} subtitle={user.email} extra={<Button>Edit</Button>} />

import { Button, Flex, Tooltip, Typography } from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';

const ViewHeader = ({ backTo, backLabel = 'Back to list', avatar, title, tags, subtitle, extra }) => {
  const navigate = useNavigate();
  return (
    <Flex justify="space-between" align="center" wrap gap={16} style={{ marginBottom: 20 }}>
      <Flex align="center" gap={14} style={{ minWidth: 0 }}>
        {backTo && (
          <Tooltip title={backLabel}>
            <Button shape="circle" icon={<ArrowLeftOutlined />} onClick={() => navigate(backTo)} aria-label={backLabel} />
          </Tooltip>
        )}
        {avatar}
        <div style={{ minWidth: 0 }}>
          <Flex align="center" gap={8} wrap>
            <Typography.Title level={4} style={{ margin: 0 }}>{title}</Typography.Title>
            {tags}
          </Flex>
          {subtitle && <Typography.Text type="secondary">{subtitle}</Typography.Text>}
        </div>
      </Flex>
      {extra && <Flex gap={8} wrap>{extra}</Flex>}
    </Flex>
  );
};

export default ViewHeader;
