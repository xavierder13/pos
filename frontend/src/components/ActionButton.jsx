// src/components/ActionButton.jsx
//
// The icon-only action button of a table row or list item (View, Edit, Delete …):
// a tonal square in the action's colour (index.css → .row-action), with its tooltip.
//
//   <ActionButton kind="view" onClick={…} />
//   <ActionButton kind="assign" title="Assign users" onClick={…} />
//   <Popconfirm …><ActionButton kind="delete" /></Popconfirm>
//
// `kind` picks the colour, the default icon and the default tooltip; `icon` and
// `title` override them. Any other prop goes to the Button, so a Popconfirm or
// Dropdown can wrap it directly.

import { Button, Tooltip } from 'antd';
import { CheckOutlined, DeleteOutlined, DownloadOutlined, EditOutlined, EyeOutlined, UsergroupAddOutlined } from '@ant-design/icons';

const KINDS = {
  view: { icon: <EyeOutlined />, title: 'View' },
  edit: { icon: <EditOutlined />, title: 'Edit' },
  assign: { icon: <UsergroupAddOutlined />, title: 'Assign' },
  download: { icon: <DownloadOutlined />, title: 'Download' },   // also print / export
  confirm: { icon: <CheckOutlined />, title: 'Confirm' },
  delete: { icon: <DeleteOutlined />, title: 'Delete' },
};

const ActionButton = ({ kind = 'view', title, icon, className = '', ...props }) => {
  const preset = KINDS[kind] ?? KINDS.view;
  const label = title ?? preset.title;
  return (
    <Tooltip title={label}>
      <Button type="text" size="small" aria-label={label} icon={icon ?? preset.icon}
        className={`row-action row-action--${kind} ${className}`.trim()} {...props} />
    </Tooltip>
  );
};

export default ActionButton;
