// src/components/inputs/LocationSelect.jsx
//
// Pick a location (store / warehouse). Loads the active locations once per page.
// Options show "CODE · Name". `allowAll` adds an "All locations" option: onChange(null) for it
// (antd options can't carry null, so it is the 'all' option inside).
// `type` limits to store | warehouse; `exclude` hides one id (a transfer's source).
// GET /locations allows location-list, inventory-list or register-use, so stock pages work without
// the Locations admin permission.

import { useEffect, useState } from 'react';
import { Select } from 'antd';
import { loadLocations } from '../../services/locations/locationCache';

const ALL = 'all';

const LocationSelect = ({ value, onChange, allowAll = false, type, exclude, ...rest }) => {
  const [locations, setLocations] = useState([]);

  useEffect(() => {
    let alive = true;
    loadLocations().then((list) => alive && setLocations(list)).catch(() => {});
    return () => { alive = false; };
  }, []);

  const options = locations
    .filter((l) => (!type || l.type === type) && l.id !== exclude)
    .map((l) => ({ value: l.id, label: `${l.code} · ${l.name}` }));

  return (
    <Select
      value={allowAll && (value === undefined || value === null) ? ALL : value}
      onChange={(v) => onChange?.(v === ALL ? null : v)}
      options={allowAll ? [{ value: ALL, label: 'All locations' }, ...options] : options}
      placeholder="Location"
      showSearch={{ optionFilterProp: 'label' }}
      {...rest}
    />
  );
};

export default LocationSelect;
