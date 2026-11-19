// src/routes/permissionRoutes.jsx
//
// The signed-in pages: { permissions, path, element }. `permissions`: any
// one of them opens the page ([] = every signed-in user; the Administrator
// opens every page). Each page also needs its entry in config/menu.jsx.
// `layout: 'register'` renders the page full screen in RegisterLayout instead of
// the back-office shell (MainLayout).
// Pages load lazily (one chunk each); MainLayout shows a spinner meanwhile.

import { lazy } from 'react';
import { REGISTER_PATH, REGISTER_PERMISSION } from '../config/menu';

const DashboardPage   = lazy(() => import('../pages/dashboard/DashboardPage'));
const ProfilePage     = lazy(() => import('../pages/profile/ProfilePage'));
const UserIndex       = lazy(() => import('../pages/administration/users/UserIndex'));
const UserView        = lazy(() => import('../pages/administration/users/UserView'));
const RoleIndex       = lazy(() => import('../pages/administration/roles/RoleIndex'));
const RoleView        = lazy(() => import('../pages/administration/roles/RoleView'));
const PermissionIndex = lazy(() => import('../pages/administration/permissions/PermissionIndex'));
const AuditTrailIndex = lazy(() => import('../pages/administration/audit_trail/AuditTrailIndex'));
const SessionSettingsPage = lazy(() => import('../pages/administration/session_settings/SessionSettingsPage'));
const NotificationsPage = lazy(() => import('../pages/notifications/NotificationsPage'));
const ApprovalIndex = lazy(() => import('../pages/approvals/ApprovalIndex'));
const ApprovalView = lazy(() => import('../pages/approvals/ApprovalView'));
const ApprovalFlowIndex = lazy(() => import('../pages/administration/approval_flows/ApprovalFlowIndex'));
const RegisterPage = lazy(() => import('../pages/register/RegisterPage'));
const LocationIndex = lazy(() => import('../pages/locations/LocationIndex'));
const LocationView = lazy(() => import('../pages/locations/LocationView'));
const CategoryIndex = lazy(() => import('../pages/categories/CategoryIndex'));
const CategoryView = lazy(() => import('../pages/categories/CategoryView'));
const ProductIndex = lazy(() => import('../pages/products/ProductIndex'));
const ProductView = lazy(() => import('../pages/products/ProductView'));
const StockIndex = lazy(() => import('../pages/inventory/StockIndex'));
const StockDocumentIndex = lazy(() => import('../pages/inventory/DocumentIndex'));
const StockDocumentView = lazy(() => import('../pages/inventory/DocumentView'));
const StockMovementIndex = lazy(() => import('../pages/inventory/MovementIndex'));
// [make:module imports]

const permissionRoutes = [
  { permissions: [], path: '/', element: <DashboardPage /> },
  { permissions: [], path: '/profile', element: <ProfilePage /> },
  { permissions: [], path: '/notifications', element: <NotificationsPage /> },
  { permissions: [], path: '/approvals', element: <ApprovalIndex /> },
  { permissions: [], path: '/approvals/:id', element: <ApprovalView /> },

  // ── Sell ──
  { permissions: [REGISTER_PERMISSION], path: REGISTER_PATH, element: <RegisterPage />, layout: 'register' },

  // ── Administration ──
  { permissions: ['user-list'], path: '/administration/users', element: <UserIndex /> },
  { permissions: ['user-list', 'user-edit'], path: '/administration/users/:id', element: <UserView /> },
  { permissions: ['role-list'], path: '/administration/roles', element: <RoleIndex /> },
  { permissions: ['role-list', 'role-edit'], path: '/administration/roles/:id', element: <RoleView /> },
  { permissions: ['permission-list'], path: '/administration/permissions', element: <PermissionIndex /> },
  { permissions: ['audit-trail-list'], path: '/administration/audit-trail', element: <AuditTrailIndex /> },
  { permissions: ['session-setting-edit'], path: '/administration/session-settings', element: <SessionSettingsPage /> },
  { permissions: ['approval-flow-edit'], path: '/administration/approval-flows', element: <ApprovalFlowIndex /> },

  { permissions: ['location-list'], path: '/locations', element: <LocationIndex /> },
  { permissions: ['location-list', 'location-edit'], path: '/locations/:id', element: <LocationView /> },
  { permissions: ['category-list'], path: '/categories', element: <CategoryIndex /> },
  { permissions: ['category-list', 'category-edit'], path: '/categories/:id', element: <CategoryView /> },
  { permissions: ['product-list'], path: '/products', element: <ProductIndex /> },
  { permissions: ['product-list', 'product-edit'], path: '/products/:id', element: <ProductView /> },
  // ── Inventory ──
  { permissions: ['inventory-list'], path: '/inventory/stock', element: <StockIndex /> },
  { permissions: ['inventory-list'], path: '/inventory/documents', element: <StockDocumentIndex /> },
  { permissions: ['inventory-list'], path: '/inventory/documents/:id', element: <StockDocumentView /> },
  { permissions: ['inventory-list'], path: '/inventory/movements', element: <StockMovementIndex /> },

  // [make:module routes] — `make module name=X` adds new modules above this line
];

export default permissionRoutes;
