// src/hooks/useUsers.js

import { useEffect } from 'react';
import useUserStore from '../store/userStore';

// withOptions: also load the role choices (needs user-create or user-edit)
const useUsers = ({ withOptions = false } = {}) => {
  const items            = useUserStore((state) => state.items);
  const total            = useUserStore((state) => state.total);
  const params           = useUserStore((state) => state.params);
  const isLoading        = useUserStore((state) => state.isLoading);
  const error            = useUserStore((state) => state.error);
  const roles            = useUserStore((state) => state.roles);
  const fetchItems       = useUserStore((state) => state.fetchItems);
  const fetchFormOptions = useUserStore((state) => state.fetchFormOptions);

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  useEffect(() => {
    if (withOptions) fetchFormOptions();
  }, [withOptions, fetchFormOptions]);

  return { items, total, params, isLoading, error, roles, fetchItems, fetchFormOptions };
};

export default useUsers;
