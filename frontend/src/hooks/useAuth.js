// src/hooks/useAuth.js
//
// Permission helpers take names one by one or as a list:
// hasAnyPermission('a', 'b') === hasAnyPermission(['a', 'b']).
// They do NOT let the Administrator through on their own — an action gate
// is `isAdmin || hasPermission('user-edit')` (product rule: the
// Administrator can do everything; the backend checks it again).

import useAuthStore from '../store/authStore';

export const ADMINISTRATOR = 'Administrator';

const useAuth = () => {
  const user            = useAuthStore((state) => state.user);
  const roles           = useAuthStore((state) => state.roles);
  const permissions     = useAuthStore((state) => state.permissions);
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const isLoaded        = useAuthStore((state) => state.isLoaded);
  const setAuth         = useAuthStore((state) => state.setAuth);
  const setUser         = useAuthStore((state) => state.setUser);
  const setToken        = useAuthStore((state) => state.setToken);
  const setLoaded       = useAuthStore((state) => state.setLoaded);
  const clearAuth       = useAuthStore((state) => state.clearAuth);

  // ── Helper functions ───────────────────────────────
  const hasRole          = (...role)       => role.flat().every((r) => roles.includes(r));
  const hasAnyRole       = (...role)       => role.flat().some((r)  => roles.includes(r));
  const hasPermission    = (...permission) => permission.flat().every((p) => permissions.includes(p));
  const hasAnyPermission = (...permission) => permission.flat().some((p)  => permissions.includes(p));
  const isAdmin          = roles.includes(ADMINISTRATOR);
  // the usual action gate: the Administrator, or every one of these permissions
  const can              = (...permission) => isAdmin || hasPermission(...permission);

  return {
    user,
    roles,
    permissions,
    isAuthenticated,
    isLoaded,
    isAdmin,
    setAuth,
    setUser,
    setToken,
    setLoaded,
    clearAuth,
    hasRole,
    hasAnyRole,
    hasPermission,
    hasAnyPermission,
    can,
  };
};

export default useAuth;
