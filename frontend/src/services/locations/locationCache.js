// src/services/locations/locationCache.js — the active locations, fetched once per page load
// (they rarely change). forgetLocations() after a location is saved.

import locationApi from './locationApi';

let cache = null;

export const loadLocations = () => {
  if (!cache) {
    cache = locationApi.getAll({ active: 1, per_page: 100, sort: 'name', order: 'asc' })
      .then(({ data }) => data.data)
      .catch((e) => { cache = null; throw e; });
  }
  return cache;
};

export const forgetLocations = () => { cache = null; };
