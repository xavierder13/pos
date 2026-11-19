// src/routes/ProtectedRoute.jsx

import { useEffect, useState } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { Button, Result, Spin } from 'antd';
import authApi from '../services/auth/authApi';
import useAuth from '../hooks/useAuth';
import { getToken } from '../utils/tokenHelper';
import { setSessionSettings } from '../utils/session';
import ChangePasswordRequired from '../components/auth/ChangePasswordRequired';

const ProtectedRoute = ({ permissions = [] }) => {
  const token = getToken();
  const { user, isLoaded, isAdmin, setAuth, setLoaded, hasAnyPermission } = useAuth();
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    if (isLoaded || !token) return;
    const loadUser = async () => {
      try {
        const { data } = await authApi.init();
        setAuth(data.user, data.user_roles, data.user_permissions);
        setSessionSettings(data.session);
        setLoaded();
      } catch (error) {
        // a 401: the axios interceptor clears the token and goes to /login
        if (error.response?.status !== 401) setFailed(true);
      }
    };
    loadUser();
  }, [isLoaded, token, setAuth, setLoaded]);

  // 1. No token → sign in
  if (!token) return <Navigate to="/login" replace />;

  // 2. The API couldn't be reached
  if (failed) {
    return (
      <div className="full-center">
        <Result status="warning" title="The server can't be reached"
          subTitle="Check your connection, then try again."
          extra={<Button type="primary" onClick={() => window.location.reload()}>Try again</Button>} />
      </div>
    );
  }

  // 3. Loading the user
  if (!isLoaded) return <div className="full-center"><Spin size="large" /></div>;

  // 4. "Require password change at next sign-in" → only the Change Password screen
  if (user?.must_change_password) return <ChangePasswordRequired />;

  // 5. The page's permissions (product rule: the Administrator opens every page)
  if (permissions.length > 0 && !isAdmin && !hasAnyPermission(...permissions)) {
    return <Navigate to="/unauthorize" replace />;
  }

  return <Outlet />;
};

export default ProtectedRoute;
