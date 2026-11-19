// src/components/MenuSearch.jsx
//
// App-wide page search: type part of a page's name or its section ("admin
// roles") and pick it to open the page. `items` are the menu entries this
// user can see (MainLayout passes them), so it never offers a hidden page.
// Ctrl+K / ⌘K focuses it.

import { useEffect, useMemo, useRef, useState } from 'react';
import { AutoComplete, Typography } from 'antd';
import { SearchOutlined } from '@ant-design/icons';

const MAX_RESULTS = 12;
const isMac = typeof navigator !== 'undefined' && /mac/i.test(navigator.platform);

// every word typed must appear in the entry's sections or label
const matches = (item, words) => {
  const haystack = [...item.sections, item.label].join(' ').toLowerCase();
  return words.every((w) => haystack.includes(w));
};

const MenuSearch = ({ items, onPick, style }) => {
  const [query, setQuery] = useState('');
  const inputRef = useRef(null);

  useEffect(() => {
    const onKeyDown = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        inputRef.current?.focus();
      }
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, []);

  const options = useMemo(() => {
    const words = query.trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (!words.length) return [];
    return items.filter((item) => matches(item, words)).slice(0, MAX_RESULTS).map((item) => ({
      value: item.key,
      label: (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '2px 0' }}>
          <span style={{ color: 'var(--accent)' }}>{item.icon}</span>
          <div style={{ lineHeight: 1.3 }}>
            <div>{item.label}</div>
            {item.sections.length > 0 && <Typography.Text type="secondary" style={{ fontSize: 11 }}>{item.sections.join(' › ')}</Typography.Text>}
          </div>
        </div>
      ),
    }));
  }, [items, query]);

  const pick = (key) => {
    const item = items.find((i) => i.key === key);
    setQuery('');
    inputRef.current?.blur();
    if (item) onPick(item);
  };

  return (
    <AutoComplete
      ref={inputRef}
      value={query}
      onChange={setQuery}
      onSelect={pick}
      options={options}
      notFoundContent={query.trim() ? 'No matching page' : null}
      popupMatchSelectWidth={320}
      allowClear
      prefix={<SearchOutlined style={{ color: 'var(--muted)' }} />}
      suffix={!query && <Typography.Text keyboard style={{ fontSize: 11 }}>{isMac ? '⌘K' : 'Ctrl K'}</Typography.Text>}
      placeholder="Search pages…"
      aria-label="Search pages"
      style={style}
    />
  );
};

export default MenuSearch;
