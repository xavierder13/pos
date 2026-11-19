// src/components/NotificationBell.jsx
//
// The header bell: unread badge (polled every minute and when the tab comes
// back, with a passive check so it never keeps an idle session alive), and a
// dropdown with the latest notifications. Clicking one marks it read and opens
// its link. "View all" → /notifications.
//
// Send one from the backend: $user->notify(new AppNotification('Title', 'Body', '/some/page', 'success'));

import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Badge, Button, Empty, Flex, Popover, Spin, Tooltip, Typography } from 'antd';
import { BellOutlined } from '@ant-design/icons';
import useNotificationStore from '../store/notificationStore';
import NotificationItem from './NotificationItem';

const POLL_MS = 60 * 1000;

const NotificationBell = () => {
  const navigate = useNavigate();
  const unread = useNotificationStore((s) => s.unread);
  const latest = useNotificationStore((s) => s.latest);
  const isLoading = useNotificationStore((s) => s.isLoading);
  const fetchUnread = useNotificationStore((s) => s.fetchUnread);
  const fetchLatest = useNotificationStore((s) => s.fetchLatest);
  const markRead = useNotificationStore((s) => s.markRead);
  const markAllRead = useNotificationStore((s) => s.markAllRead);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    fetchUnread();
    const id = setInterval(fetchUnread, POLL_MS);
    const onVisible = () => document.visibilityState === 'visible' && fetchUnread();
    document.addEventListener('visibilitychange', onVisible);
    return () => { clearInterval(id); document.removeEventListener('visibilitychange', onVisible); };
  }, [fetchUnread]);

  const onOpenChange = (next) => {
    setOpen(next);
    if (next) fetchLatest();
  };

  const openItem = async (item) => {
    setOpen(false);
    if (!item.read_at) markRead(item.id).catch(() => {});
    if (item.link) navigate(item.link);
  };

  const content = (
    <div style={{ width: 360, maxWidth: 'calc(100vw - 32px)' }}>
      <Flex justify="space-between" align="center" style={{ padding: '4px 4px 10px' }}>
        <Typography.Text strong>Notifications</Typography.Text>
        <Button type="link" size="small" disabled={!unread} onClick={markAllRead}>Mark all as read</Button>
      </Flex>
      <Spin spinning={isLoading && !latest.length}>
        <div style={{ maxHeight: 380, overflowY: 'auto', margin: '0 -12px' }}>
          {latest.length
            ? latest.map((n) => <NotificationItem key={n.id} item={n} onOpen={openItem} compact />)
            : <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="You're all caught up" style={{ margin: '24px 0' }} />}
        </div>
      </Spin>
      <Button block type="text" style={{ marginTop: 8 }} onClick={() => { setOpen(false); navigate('/notifications'); }}>View all</Button>
    </div>
  );

  return (
    <Popover content={content} trigger="click" placement="bottomRight" open={open} onOpenChange={onOpenChange} arrow={false}>
      <Tooltip title="Notifications" open={open ? false : undefined}>
        <Badge count={unread} size="small" overflowCount={99} offset={[-4, 4]}>
          <Button type="text" shape="circle" icon={<BellOutlined style={{ fontSize: 18 }} />} aria-label={`Notifications${unread ? `, ${unread} unread` : ''}`} />
        </Badge>
      </Tooltip>
    </Popover>
  );
};

export default NotificationBell;
