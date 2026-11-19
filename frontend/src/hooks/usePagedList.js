// src/hooks/usePagedList.js — a server-paged list kept in the page (for lists no other page shares):
//   const list = usePagedList(inventoryApi.documents, { type: undefined });
//   list.items · list.total · list.params · list.loading · list.reload(changes)
// reload(changes) merges into params (page resets to 1 unless `page` is given); the effect fetches.

import { useCallback, useEffect, useState } from 'react';
import { App } from 'antd';
import handleApiError from '../utils/handleApiError';

const usePagedList = (fetcher, initial = {}) => {
  const { message } = App.useApp();
  const [params, setParams] = useState(() => ({ page: 1, per_page: 20, ...initial }));
  const [tick, setTick] = useState(0);   // forces a refetch with unchanged params
  const [state, setState] = useState({ items: [], total: 0, loading: true });

  useEffect(() => {
    let alive = true;
    const run = async () => {
      setState((s) => ({ ...s, loading: true }));
      try {
        const { data } = await fetcher(params);
        if (alive) setState({ items: data.data, total: data.meta.total, loading: false });
      } catch (error) {
        if (!alive) return;
        handleApiError(error, message);
        setState((s) => ({ ...s, loading: false }));
      }
    };
    run();
    return () => { alive = false; };
  }, [fetcher, params, tick, message]);

  const reload = useCallback((changes = {}) => {
    setParams((p) => ({ ...p, page: 1, ...changes }));
    setTick((t) => t + 1);
  }, []);

  return { ...state, params, reload };
};

export default usePagedList;
