// src/store/userStore.js
//
// Users are paged, filtered and sorted server-side: `params` holds the
// current page / filters; fetchItems(changes) merges changes and reloads.

import { create } from 'zustand';
import userApi from '../services/administration/userApi';

export const DEFAULT_USER_PARAMS = { page: 1, per_page: 20, search: '', active: undefined, role_id: undefined, sort: undefined, order: undefined };

const useUserStore = create((set, get) => ({

  // ── State ──────────────────────────────────────────
  items:     [],
  total:     0,
  params:    DEFAULT_USER_PARAMS,
  isLoading: false,
  error:     null,
  roles:     [],

  // ── Actions ────────────────────────────────────────
  fetchItems: async (changes = {}) => {
    const params = { ...get().params, ...changes };
    set({ params, isLoading: true, error: null });
    try {
      const { data } = await userApi.getAll(params);
      set({ items: data.data, total: data.meta.total });
    } catch (error) {
      set({ error: error.response?.data?.message || 'Failed to load users.' });
    } finally {
      set({ isLoading: false });
    }
  },

  fetchFormOptions: async () => {
    try {
      const { data } = await userApi.formOptions();
      set({ roles: data.roles });
    } catch {
      // no user-create / user-edit: no options — the page hides the role filter
    }
  },

}));

export default useUserStore;
