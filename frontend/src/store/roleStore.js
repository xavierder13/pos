// src/store/roleStore.js
//
// Roles are few: the whole list at once, filtered / sorted client-side.

import { create } from 'zustand';
import roleApi from '../services/administration/roleApi';

const useRoleStore = create((set) => ({

  // ── State ──────────────────────────────────────────
  items:       [],
  permissions: [],
  isLoading:   false,
  error:       null,

  // ── Actions ────────────────────────────────────────
  fetchItems: async () => {
    set({ isLoading: true, error: null });
    try {
      const { data } = await roleApi.getAll();
      set({ items: data.data });
    } catch (error) {
      set({ error: error.response?.data?.message || 'Failed to load roles.' });
    } finally {
      set({ isLoading: false });
    }
  },

  fetchFormOptions: async () => {
    try {
      const { data } = await roleApi.formOptions();
      set({ permissions: data.permissions });
    } catch {
      // no role-create / role-edit
    }
  },

}));

export default useRoleStore;
