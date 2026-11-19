// src/config/menu.jsx
//
// The sidebar menu and every page's title / breadcrumb, in one place.
// A page needs: its route in routes/permissionRoutes.jsx AND its entry here.
//
// Entry: { key, label, icon?, path?, permissions?, children? }
// - permissions: any one of them shows the entry ([] / absent = everyone;
//   the Administrator sees everything)
// - a group (children) shows when one of its children does
// - { type: 'group', key, label, children } is a section heading in the sidebar
//   (Sell / Back office / Settings), not a clickable entry

import {
  DatabaseOutlined, EnvironmentOutlined, FileDoneOutlined, HistoryOutlined, TagsOutlined,
  AppstoreOutlined, AuditOutlined, CheckSquareOutlined, ClockCircleOutlined, DashboardOutlined, NodeIndexOutlined, KeyOutlined, SafetyOutlined, SettingOutlined, ShopOutlined, TeamOutlined,
} from '@ant-design/icons';

// the cashier's sell screen: full screen, outside the back-office shell
export const REGISTER_PATH = '/register';
export const REGISTER_PERMISSION = 'register-use';

const menu = [
  { key: 'dashboard', label: 'Dashboard', icon: <DashboardOutlined />, path: '/' },
  {
    type: 'group',
    key: 'sell',
    label: 'Sell',
    children: [
      { key: 'register', label: 'Register', icon: <ShopOutlined />, path: REGISTER_PATH, permissions: [REGISTER_PERMISSION] },
    ],
  },
  {
    type: 'group',
    key: 'inventory',
    label: 'Inventory',
    children: [
      { key: 'stock', label: 'Stock', icon: <DatabaseOutlined />, path: '/inventory/stock', permissions: ['inventory-list'] },
      { key: 'stock-documents', label: 'Stock documents', icon: <FileDoneOutlined />, path: '/inventory/documents', permissions: ['inventory-list'] },
      { key: 'stock-movements', label: 'Movements', icon: <HistoryOutlined />, path: '/inventory/movements', permissions: ['inventory-list'] },
    ],
  },
  {
    type: 'group',
    key: 'catalog',
    label: 'Catalog',
    children: [
      { key: 'products', label: 'Products', icon: <TagsOutlined />, path: '/products', permissions: ['product-list'] },
      { key: 'categories', label: 'Categories', icon: <AppstoreOutlined />, path: '/categories', permissions: ['category-list'] },
    ],
  },
  {
    type: 'group',
    key: 'back-office',
    label: 'Back office',
    children: [
      { key: 'approvals', label: 'Approvals', icon: <CheckSquareOutlined />, path: '/approvals' },
      // [make:module menu] — generated modules' entries go above this line
    ],
  },
  {
    type: 'group',
    key: 'settings',
    label: 'Settings',
    children: [
      { key: 'locations', label: 'Locations', icon: <EnvironmentOutlined />, path: '/locations', permissions: ['location-list'] },
      {
        key: 'administration',
        label: 'Administration',
        icon: <SettingOutlined />,
        children: [
          { key: 'users', label: 'Users', icon: <TeamOutlined />, path: '/administration/users', permissions: ['user-list'] },
          { key: 'roles', label: 'Roles', icon: <SafetyOutlined />, path: '/administration/roles', permissions: ['role-list'] },
          { key: 'permissions', label: 'Permissions', icon: <KeyOutlined />, path: '/administration/permissions', permissions: ['permission-list'] },
          { key: 'audit-trail', label: 'Audit Trail', icon: <AuditOutlined />, path: '/administration/audit-trail', permissions: ['audit-trail-list'] },
          { key: 'approval-flows', label: 'Approval Flows', icon: <NodeIndexOutlined />, path: '/administration/approval-flows', permissions: ['approval-flow-edit'] },
          { key: 'session-settings', label: 'Session Settings', icon: <ClockCircleOutlined />, path: '/administration/session-settings', permissions: ['session-setting-edit'] },
        ],
      },
    ],
  },
];

// pages not in the menu: path → title
export const extraTitles = {
  '/profile': 'My Profile',
  '/notifications': 'Notifications',
  '/unauthorize': 'Unauthorized',
};

// a record's pages under a menu entry (view / edit …): the entry stays
// selected and the breadcrumb ends in `label`. Add one per detail route.
export const detailRoutes = [
  { pattern: /^\/administration\/users\/\d+$/, parent: '/administration/users', label: 'View User' },
  { pattern: /^\/administration\/roles\/\d+$/, parent: '/administration/roles', label: 'View Role' },
  { pattern: /^\/approvals\/\d+$/, parent: '/approvals', label: 'Request' },
  { pattern: /^\/locations\/\d+$/, parent: '/locations', label: 'View Location' },
  { pattern: /^\/categories\/\d+$/, parent: '/categories', label: 'View Category' },
  { pattern: /^\/products\/\d+$/, parent: '/products', label: 'View Product' },
  { pattern: /^\/inventory\/documents\/\d+$/, parent: '/inventory/documents', label: 'Document' },
  // [make:module detail]
];

const findEntry = (path, entries, trail) => {
  for (const entry of entries) {
    if (entry.path === path) return [...trail, entry];
    if (entry.children) {
      const found = findEntry(path, entry.children, [...trail, entry]);
      if (found) return found;
    }
  }
  return null;
};

// [ancestors…, entry] for a path, from the menu tree; a detail route adds a
// last { key: <parent's key>, label, detail: true } after its parent entry
export const findTrail = (path) => {
  const exact = findEntry(path, menu, []);
  if (exact) return exact;
  const detail = detailRoutes.find((d) => d.pattern.test(path));
  if (!detail) return null;
  const parent = findEntry(detail.parent, menu, []);
  return parent ? [...parent, { key: parent.at(-1).key, label: detail.label, detail: true }] : null;
};

// every page entry (with path) and its section labels — for the menu search
export const flattenMenu = (entries = menu, sections = []) => entries.flatMap((entry) => (entry.children
  ? flattenMenu(entry.children, [...sections, entry.label])
  : [{ ...entry, sections }]));

export default menu;
