// src/layouts/MainLayout.jsx
//
// The back-office shell: a collapsible navy sidebar (config/menu.jsx, filtered by
// permission, in Sell / Back office / Settings sections), a sticky header with the
// breadcrumb, page search, the connection + clock status, an "Open Register"
// button for anyone who may sell, notifications and the user menu, then the page.
// The register itself is full screen (layouts/RegisterLayout.jsx).

import { Suspense, useEffect, useState } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { Avatar, Breadcrumb, Button, Dropdown, Flex, Grid, Layout, Menu, Spin, Typography } from 'antd';
import { BellOutlined, LogoutOutlined, MenuFoldOutlined, MenuUnfoldOutlined, ShopOutlined, UserOutlined } from '@ant-design/icons';
import menu, { REGISTER_PATH, REGISTER_PERMISSION, extraTitles, findTrail, flattenMenu } from '../config/menu';
import MenuSearch from '../components/MenuSearch';
import NotificationBell from '../components/NotificationBell';
import SessionTimeout from '../components/SessionTimeout';
import StatusClock from '../components/StatusClock';
import useAuth from '../hooks/useAuth';
import useSignOut from '../hooks/useSignOut';
import PageErrorBoundary from '../components/PageErrorBoundary';
import { APP_NAME } from '../utils/brand';
import { initials } from '../utils/initials';

const { Header, Sider, Content } = Layout;

const COLLAPSED_KEY = 'sider_collapsed';
const readCollapsed = () => {
  try { return localStorage.getItem(COLLAPSED_KEY) === '1'; } catch { return false; }
};

const MainLayout = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { user, roles, isAdmin, can, hasAnyPermission } = useAuth();
  const { signOut, confirmSignOut } = useSignOut();
  const screens = Grid.useBreakpoint();
  const isMobile = !screens.md;
  const [collapsed, setCollapsed] = useState(readCollapsed);
  // open sections; none while collapsed (an inline menu would show their items under the icons)
  const [openKeys, setOpenKeys] = useState(() => (findTrail(location.pathname) || []).slice(0, -1).map((e) => e.key));

  const toggle = () => {
    setCollapsed((value) => {
      try { localStorage.setItem(COLLAPSED_KEY, value ? '0' : '1'); } catch { /* storage blocked */ }
      return !value;
    });
  };

  // the menu entries this user may see (the Administrator sees everything)
  const visible = (entry) => isAdmin || !entry.permissions?.length || hasAnyPermission(entry.permissions);
  const build = (entries) => entries.flatMap((entry) => {
    if (entry.children) {
      const children = build(entry.children);
      if (!children.length) return [];
      return entry.type === 'group'
        ? [{ type: 'group', key: entry.key, label: entry.label, children }]
        : [{ key: entry.key, icon: entry.icon, label: entry.label, children }];
    }
    if (!visible(entry)) return [];
    return [{ key: entry.key, icon: entry.icon, label: <Link to={entry.path}>{entry.label}</Link> }];
  });
  const items = build(menu);
  const searchable = flattenMenu().filter(visible);
  const canSell = can(REGISTER_PERMISSION);

  const trail = findTrail(location.pathname);
  const selectedKey = trail?.at(-1)?.key;
  const title = trail?.at(-1)?.label ?? extraTitles[location.pathname];
  const breadcrumb = [
    { title: <Link to="/">Home</Link> },
    // a menu entry with a page links back to it (from its detail pages)
    ...(trail && selectedKey !== 'dashboard'
      ? trail.map((e, i) => ({ title: e.path && i < trail.length - 1 ? <Link to={e.path}>{e.label}</Link> : e.label }))
      : []),
    ...(!trail && title ? [{ title }] : []),
  ];

  // the browser tab says where you are
  useEffect(() => {
    document.title = title ? `${title} · ${APP_NAME}` : APP_NAME;
  }, [title]);

  const userMenu = {
    items: [
      { key: 'who', disabled: true, label: <div><Typography.Text strong>{user?.name}</Typography.Text><br /><Typography.Text type="secondary">{user?.email}</Typography.Text></div> },
      { type: 'divider' },
      { key: 'profile', icon: <UserOutlined />, label: 'My Profile', onClick: () => navigate('/profile') },
      { key: 'notifications', icon: <BellOutlined />, label: 'Notifications', onClick: () => navigate('/notifications') },
      { key: 'logout', icon: <LogoutOutlined />, label: 'Sign out', danger: true, onClick: () => confirmSignOut() },
    ],
  };

  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Sider className={`app-sider${collapsed ? ' is-collapsed' : ''}`} collapsible collapsed={collapsed} trigger={null} width={248}
        breakpoint="md" collapsedWidth={isMobile ? 0 : 76} onBreakpoint={(broken) => broken && setCollapsed(true)}
        style={{ position: 'sticky', top: 0, height: '100vh', overflow: 'auto' }}>
        <div className="app-brand">
          <span className="app-logo">{APP_NAME.charAt(0).toUpperCase()}</span>
          {!collapsed && (
            <span className="app-brand__text">
              <span>{APP_NAME}</span>
              <small>Back office</small>
            </span>
          )}
        </div>
        {canSell && (
          <div className="sider-cta">
            <Button type="primary" block icon={<ShopOutlined />} onClick={() => navigate(REGISTER_PATH)}
              aria-label="Open Register" title={collapsed ? 'Open Register' : undefined}>
              {!collapsed && 'Open Register'}
            </Button>
          </div>
        )}
        <Menu theme="dark" mode="inline" items={items}
          selectedKeys={selectedKey ? [selectedKey] : []}
          openKeys={collapsed ? [] : openKeys} onOpenChange={(keys) => !collapsed && setOpenKeys(keys)}
          onClick={() => isMobile && setCollapsed(true)} />
        {!collapsed && <div className="app-sider__footer">v{import.meta.env.VITE_APP_VERSION || '1.0'} · {roles[0] ?? 'Signed in'}</div>}
      </Sider>

      <Layout>
        <Header className="app-header">
          <Button type="text" onClick={toggle} icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />} aria-label="Toggle menu" />
          <Breadcrumb items={breadcrumb} style={{ flex: 1, minWidth: 0 }} />
          {screens.xl && <MenuSearch items={searchable} onPick={(item) => navigate(item.path)} style={{ width: 240 }} />}
          {screens.sm && <StatusClock compact={!screens.lg} />}
          {canSell && isMobile && (
            <Button type="primary" icon={<ShopOutlined />} onClick={() => navigate(REGISTER_PATH)} aria-label="Open Register" />
          )}
          <NotificationBell />
          <Dropdown menu={userMenu} placement="bottomRight" trigger={['click']}>
            <div className="app-user">
              <Avatar size={30} className="app-avatar">
                {initials(user?.name)}
              </Avatar>
              {screens.lg && <Typography.Text>{user?.name}</Typography.Text>}
            </div>
          </Dropdown>
        </Header>

        <SessionTimeout onSignOut={signOut} />
        <Content className="app-content" style={{ padding: isMobile ? 12 : 28 }}>
          <PageErrorBoundary resetKey={location.pathname}>
            <Suspense fallback={<Flex justify="center" style={{ padding: 48 }}><Spin /></Flex>}>
              <Outlet />
            </Suspense>
          </PageErrorBoundary>
        </Content>
      </Layout>
    </Layout>
  );
};

export default MainLayout;
