// src/layouts/RegisterLayout.jsx
//
// The register's full-screen shell: no sidebar, nothing between the cashier and
// the sale. A slim navy bar carries the brand, the connection + clock status, who
// is on the till, a way back to the back office (if they may use it) and sign out.
// The idle timer runs here too: an unattended till signs itself out.

import { Suspense, useEffect } from 'react';
import { Link, Outlet } from 'react-router-dom';
import { Avatar, Button, Flex, Spin, Tooltip } from 'antd';
import { AppstoreOutlined, LogoutOutlined } from '@ant-design/icons';
import SessionTimeout from '../components/SessionTimeout';
import StatusClock from '../components/StatusClock';
import PageErrorBoundary from '../components/PageErrorBoundary';
import useAuth from '../hooks/useAuth';
import useSignOut from '../hooks/useSignOut';
import { APP_NAME } from '../utils/brand';
import { initials } from '../utils/initials';
import '../pages/register/register.css';

const RegisterLayout = () => {
  const { user, roles } = useAuth();
  const { signOut, confirmSignOut } = useSignOut();

  useEffect(() => {
    document.title = `Register · ${APP_NAME}`;
  }, []);

  return (
    <div className="reg-shell">
      <header className="reg-bar">
        <Link to="/" className="reg-bar__brand" aria-label="Back office">
          <span className="app-logo">{APP_NAME.charAt(0).toUpperCase()}</span>
          <span>{APP_NAME}</span>
          <span className="reg-bar__tag">Register</span>
        </Link>
        <div className="reg-bar__spacer" />
        <StatusClock />
        <div className="reg-bar__cashier">
          <Avatar size={30} className="app-avatar">{initials(user?.name)}</Avatar>
          <span className="reg-bar__who">
            <strong>{user?.name}</strong>
            <small>{roles[0] ?? 'Cashier'}</small>
          </span>
        </div>
        <Tooltip title="Back office">
          <Link to="/"><Button className="reg-bar__btn" icon={<AppstoreOutlined />} aria-label="Back office" /></Link>
        </Tooltip>
        <Tooltip title="Sign out">
          <Button className="reg-bar__btn" icon={<LogoutOutlined />} aria-label="Sign out"
            onClick={() => confirmSignOut('A sale in progress on this screen will be lost.')} />
        </Tooltip>
      </header>

      <SessionTimeout onSignOut={signOut} />
      <main className="reg-main">
        <PageErrorBoundary resetKey="register">
          <Suspense fallback={<Flex justify="center" style={{ padding: 48 }}><Spin /></Flex>}>
            <Outlet />
          </Suspense>
        </PageErrorBoundary>
      </main>
    </div>
  );
};

export default RegisterLayout;
