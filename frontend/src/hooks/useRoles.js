// src/hooks/useRoles.js

import { useEffect } from 'react';
import useRoleStore from '../store/roleStore';

const useRoles = ({ withOptions = false } = {}) => {
  const items            = useRoleStore((state) => state.items);
  const permissions      = useRoleStore((state) => state.permissions);
  const isLoading        = useRoleStore((state) => state.isLoading);
  const error            = useRoleStore((state) => state.error);
  const fetchItems       = useRoleStore((state) => state.fetchItems);
  const fetchFormOptions = useRoleStore((state) => state.fetchFormOptions);

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  useEffect(() => {
    if (withOptions) fetchFormOptions();
  }, [withOptions, fetchFormOptions]);

  return { items, permissions, isLoading, error, fetchItems, fetchFormOptions };
};

export default useRoles;
