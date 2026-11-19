// src/pages/notifications/NotificationsPage.jsx — all of the signed-in user's notifications.

import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { App, Button, Card, Empty, Flex, Pagination, Popconfirm, Segmented, Spin, Tooltip } from 'antd';
import ActionButton from '../../components/ActionButton';
import { CheckOutlined, ReloadOutlined } from '@ant-design/icons';
import PageHeader from '../../components/PageHeader';
import NotificationItem from '../../components/NotificationItem';
import notificationApi from '../../services/notificationApi';
import useNotificationStore from '../../store/notificationStore';
import handleApiError from '../../utils/handleApiError';

const PER_PAGE = 15;

const NotificationsPage = () => {
  const { message } = App.useApp();
  const navigate = useNavigate();
  const setUnread = useNotificationStore((s) => s.setUnread);
  const unread = useNotificationStore((s) => s.unread);
  const [filter, setFilter] = useState('all');
  const [page, setPage] = useState(1);
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await notificationApi.getAll({ unread: filter === 'unread' ? 1 : undefined, page, per_page: PER_PAGE });
      setRows(data.data);
      setTotal(data.meta.total);
      setUnread(data.unread_count);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [filter, page, message, setUnread]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const act = async (call) => {
    try {
      const { data } = await call();
      setUnread(data.unread_count);
      load();
    } catch (error) {
      handleApiError(error, message);
    }
  };

  const open = async (item) => {
    if (!item.read_at) await act(() => notificationApi.markRead(item.id));
    if (item.link) navigate(item.link);
  };

  return (
    <>
      <PageHeader title="Notifications" subtitle={unread ? `${unread} unread` : 'All caught up'} extra={<>
        <Tooltip title="Reload"><Button icon={<ReloadOutlined />} onClick={load} /></Tooltip>
        <Button icon={<CheckOutlined />} disabled={!unread} onClick={() => act(notificationApi.markAllRead)}>Mark all as read</Button>
      </>} />
      <Segmented value={filter} onChange={(v) => { setFilter(v); setPage(1); }} style={{ marginBottom: 16 }}
        options={[{ value: 'all', label: 'All' }, { value: 'unread', label: 'Unread' }]} />
      <Card styles={{ body: { padding: 8 } }}>
        <Spin spinning={loading}>
          {rows.length ? rows.map((n) => (
            <Flex key={n.id} align="center" gap={8}>
              <div style={{ flex: 1, minWidth: 0 }}><NotificationItem item={n} onOpen={open} /></div>
              <Flex gap={6} style={{ paddingRight: 8 }}>
                {!n.read_at && (
                  <ActionButton kind="confirm" title="Mark as read" onClick={() => act(() => notificationApi.markRead(n.id))} />
                )}
                <Popconfirm title="Delete this notification?" okText="Delete" okButtonProps={{ danger: true }} onConfirm={() => act(() => notificationApi.remove(n.id))}>
                  <ActionButton kind="delete" />
                </Popconfirm>
              </Flex>
            </Flex>
          )) : !loading && <Empty description={filter === 'unread' ? 'No unread notifications' : 'No notifications yet'} style={{ margin: '40px 0' }} />}
        </Spin>
      </Card>
      {total > PER_PAGE && <Pagination style={{ marginTop: 16, textAlign: 'right' }} current={page} pageSize={PER_PAGE} total={total} onChange={setPage} showSizeChanger={false} />}
    </>
  );
};

export default NotificationsPage;
