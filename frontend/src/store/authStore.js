// src/store/authStore.js
//
// The signed-in user: filled once per page load from GET /auth/init
// (ProtectedRoute) or from the login response (LoginForm).

import { create } from 'zustand'
import { setToken, clearTokens } from '../utils/tokenHelper'

const useAuthStore = create((set) => ({

  // ── State ──────────────────────────────────────────
  user:            null,
  roles:           [],
  permissions:     [],
  isAuthenticated: false,
  isLoaded:        false,

  // ── Actions ────────────────────────────────────────
  setAuth: (user, roles, permissions) => set({
    user,
    roles,
    permissions,
    isAuthenticated: true,
  }),

  setUser: (user) => set({ user }),

  setToken: (accessToken) => setToken(accessToken),

  setLoaded: () => set({ isLoaded: true }),

  clearAuth: () => {
    clearTokens()
    set({
      user:            null,
      roles:           [],
      permissions:     [],
      isAuthenticated: false,
      isLoaded:        false,
    })
  },

}))

export default useAuthStore
