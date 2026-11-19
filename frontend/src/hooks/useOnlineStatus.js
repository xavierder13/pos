// src/hooks/useOnlineStatus.js — true while the browser has a network connection.
// (navigator.onLine only knows the device has a network, not that the API answers.)

import { useSyncExternalStore } from 'react';

const subscribe = (callback) => {
  window.addEventListener('online', callback);
  window.addEventListener('offline', callback);
  return () => {
    window.removeEventListener('online', callback);
    window.removeEventListener('offline', callback);
  };
};

const useOnlineStatus = () => useSyncExternalStore(subscribe, () => navigator.onLine, () => true);

export default useOnlineStatus;
