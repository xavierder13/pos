// src/components/ExpandIcon.jsx
//
// Row expand toggle for AntD tables: a circled chevron that points right
// when collapsed and turns down when expanded. Rows that can't expand keep an
// empty slot of the same width so the column stays aligned.
//   <Table components={{ ExpandIcon }} expandable={{ expandedRowRender, ... }} />
// (antd 6.7+: `components.ExpandIcon` — `expandable.expandIcon` is deprecated.)

import { Button, Tooltip, theme } from 'antd';
import { RightOutlined } from '@ant-design/icons';

const ExpandIcon = ({ expanded, expandable = true, onClick, onExpand, record, type = 'row' }) => {
  const { token } = theme.useToken();

  if (!expandable && type === 'row') return <span style={{ display: 'inline-block', width: 24 }} />;

  const label = type === 'all' ? (expanded ? 'Collapse all' : 'Expand all') : (expanded ? 'Hide details' : 'Show details');
  const toggle = (e) => {
    e.stopPropagation();
    if (onClick) onClick(e);
    else onExpand?.(record, e);
  };

  return (
    <Tooltip title={label} mouseEnterDelay={0.4}>
      <Button
        size="small"
        shape="circle"
        color={expanded ? 'primary' : 'default'}
        variant={expanded ? 'filled' : 'outlined'}
        aria-expanded={expanded}
        aria-label={label}
        onClick={toggle}
        style={expanded ? { borderColor: token.colorPrimary } : { color: token.colorTextSecondary }}
        icon={<RightOutlined style={{ fontSize: 12, transition: `transform ${token.motionDurationMid}`, transform: expanded ? 'rotate(90deg)' : 'none' }} />}
      />
    </Tooltip>
  );
};

export default ExpandIcon;
