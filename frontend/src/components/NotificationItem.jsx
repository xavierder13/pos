// src/components/NotificationItem.jsx — one notification row (the bell's dropdown and the Notifications page).

import { Typography } from 'antd';
import { COLORS } from '../utils/theme';
import { CheckCircleFilled, CloseCircleFilled, ExclamationCircleFilled, InfoCircleFilled } from '@ant-design/icons';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const TYPE_ICONS = {
  success: <CheckCircleFilled style={{ color: COLORS.success }} />,
  warning: <ExclamationCircleFilled style={{ color: COLORS.warning }} />,
  error: <CloseCircleFilled style={{ color: COLORS.error }} />,
  info: <InfoCircleFilled style={{ color: 'var(--accent)' }} />,
};

const NotificationItem = ({ item, onOpen, compact }) => (
  <div className={`notif-item${item.read_at ? '' : ' is-unread'}`} onClick={() => onOpen(item)} role="button" tabIndex={0}
    onKeyDown={(e) => e.key === 'Enter' && onOpen(item)}>
    <span className="notif-item__icon">{TYPE_ICONS[item.type] ?? TYPE_ICONS.info}</span>
    <div style={{ minWidth: 0, flex: 1 }}>
      <div className="notif-item__title">{item.title}</div>
      {item.body && <Typography.Paragraph type="secondary" ellipsis={compact ? { rows: 2 } : false} style={{ margin: 0, fontSize: 13 }}>{item.body}</Typography.Paragraph>}
      <Typography.Text type="secondary" style={{ fontSize: 12 }}>{dayjs(item.created_at).fromNow()}</Typography.Text>
    </div>
    {!item.read_at && <span className="notif-item__dot" />}
  </div>
);


export default NotificationItem;
