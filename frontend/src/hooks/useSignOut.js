// src/hooks/useSignOut.js — sign out (token revoked, back to /login), and the
// "Sign out?" confirm in front of it. Shared by the back-office shell and the register.

import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { App } from 'antd';
import useAuth from './useAuth';
import authApi from '../services/auth/authApi';

const useSignOut = () => {
  const navigate = useNavigate();
  const { modal } = App.useApp();
  const { clearAuth } = useAuth();

  const signOut = useCallback(async () => {
    try { await authApi.logout(); } catch { /* signed out locally anyway */ }
    clearAuth();
    navigate('/login', { replace: true });
  }, [clearAuth, navigate]);

  const confirmSignOut = useCallback((content) => modal.confirm({
    title: 'Sign out?', content, okText: 'Sign out', onOk: signOut,
  }), [modal, signOut]);

  return { signOut, confirmSignOut };
};

export default useSignOut;
