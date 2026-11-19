# CLAUDE.md: frontend

React 19 SPA for the Laravel API in `../backend`. Vite 8, Ant Design 6,
React Router 7, Zustand 5, axios, dayjs. Plain JavaScript (JSX), ESLint flat
config, no test framework yet.

## Running it: Docker only

```
docker compose up -d --build                       # Vite dev → http://localhost:5210
docker compose exec web-dev npm run lint
docker compose exec web-dev npm run build
docker compose --profile prod up -d --build        # nginx build → http://localhost:3210
```

`VITE_API_BASE_URL` (in `.env`) is the API, e.g. `http://localhost:8210/api`. Vite bakes
`VITE_*` in at build time, so the production image takes them as build args.

## Layout

```
src/api/axiosInstance.js              the one axios instance (token, JSON, 401 → /login)
src/services/<module>/<x>Api.js       thin wrappers; the header comment documents the endpoints
src/store/<x>Store.js                 one Zustand store per resource
src/hooks/use<X>.js                   wraps a store with selectors and fetches on mount
src/pages/<module>/<x>/...            route-level pages (+ their dialogs)
src/routes/permissionRoutes.jsx       every signed-in page: { permissions, path, element } (lazy)
src/config/menu.jsx                   sidebar entries + titles/breadcrumbs
src/layouts/MainLayout.jsx            the back-office shell (menu sections, Open Register, online pill + clock)
src/layouts/RegisterLayout.jsx        the register's full-screen shell (routes with layout: 'register')
src/pages/register/                   the sell screen: useTicket (sale + per-tax-class totals), PaymentModal; data from /register/catalog
src/pages/inventory/                  Stock, Stock documents (+ view), Movements; StockDocumentModal (receive / adjust / transfer)
src/components/inputs/                MoneyInput (pesos in, centavos out), LocationSelect, ProductPicker
src/hooks/usePagedList.js             a server-paged list kept in the page (lists no other page shares)
src/utils/tax.js, quantity.js         VAT + tax classes; formatQty (decimal quantities + unit)
src/utils/money.js                    formatMoney / toMinor / toMajor: money is integer cents (VITE_CURRENCY, VITE_LOCALE)
src/components/                       shared UI: PageHeader, ViewHeader, ViewSection, AuditHistory, RecordNotFound,
                                      ExpandIcon, MenuSearch (Ctrl+K), PageErrorBoundary, AppProviders, auth/ (login)
src/utils/                            handleApiError, formErrors, downloadFile, permissionGroups, tokenHelper, formatDate, theme
```

## The page flow (from sap-b1-web / hris)

`List → View → Edit`, every step reachable by URL:

- **List** (`UserIndex`): filters + table. The name links to the view page; row actions follow
  the colour rule below. A quick look that needs no page (a user's roles, a role's permissions)
  is a modal opened from a tag in the row.
- **View** (`UserView`, `RoleView`) at `/<module>/<thing>s/:id`: loads by the `:id` param (works on
  a direct link / refresh), `ViewHeader` (Back, title, status tags, actions), sections in
  `ViewSection` cards, `AuditHistory` at the bottom, `RecordNotFound` on a 404. Register the route in
  `permissionRoutes.jsx` and its breadcrumb in `config/menu.jsx` → `detailRoutes`.
- **Edit / Add**: a dialog (`useModalForm`) opened from the list or the view page; after saving,
  the page reloads its record.

## Sessions & notifications

- `components/SessionTimeout.jsx` (in MainLayout) is the idle timer: Session Settings come from
  login / init (`utils/session.js`), so they're never hard-coded. A background call that must
  not count as activity sends `PASSIVE_HEADERS` (see `notificationApi.unreadCount`).
- `user.must_change_password` → `ProtectedRoute` shows only `components/auth/ChangePasswordRequired`.
- `components/NotificationBell.jsx` + `store/notificationStore.js` + `pages/notifications/`.

## Attachments & approvals on a view page

`<Attachments type="<key>" recordId={id} />` and `<ApprovalPanel type="<key>" recordId={id} />`
(`components/`), where `<key>` is the record's `RecordTypes` key in the backend. Both check
`<key>-list` / `<key>-edit` themselves. The approval pages are `pages/approvals/` and the flow
setup is `pages/administration/approval_flows/`.

**Form.List in a dialog:** don't use `useModalForm` (its `preserve={false}` wipes list rows). Make
the dialog its own component, mounted only while open, with its own `Form.useForm()`
(see `ApprovalFlowFormModal`).

## Look

A **light** theme from the reference palette navy `#172A39` · warm off-white `#E9E4E0` · coral
`#FC563C` · slate grey `#6E7575`: off-white pages, light cards, navy text, coral accent (links use
the deeper `#E03E25` to read as text), and two navy "brand" panels, the sidebar and the login
showcase. AntD runs the default (light) algorithm; the sidebar menu is AntD's dark menu painted navy.

`COLORS` in `utils/theme.js` is the only palette: AntD reads it through `antdTheme`, and
`applyCssVars()` (in `main.jsx`) writes it onto `:root` as CSS variables (`accentSoft` →
`--accent-soft`, `onNavyMuted` → `--on-navy-muted`). Change colours there only (the one exception:
the literal page colours on `body` in `index.css`, painted before the script runs). `black` keeps its
name across the theme copies but is the off-white page here. Inside a navy panel (`.app-sider`,
`.login-showcase`) the CSS redefines `--text`, `--muted` and `--border` to their `on-navy` values,
so shared rules work in both. In JSX, use `COLORS.success` / `.warning` / `.error` (or AntD's
`type`/`status` props), never a hex.

Containers `pos_web_*`, ports dev 5210 / prod 3210.

## Conventions

- **Row actions**: `<ActionButton kind="view|edit|assign|download|confirm|delete" onClick={…} />`
  (`components/ActionButton.jsx`), inside a `Space size={6}`. It is the icon button + its tooltip;
  `kind` sets the colour, icon and tooltip (`title` / `icon` override them). Wrap a delete in a
  `Popconfirm` directly: `<Popconfirm …><ActionButton kind="delete" /></Popconfirm>`. Colours are
  `COLORS.action*` in `utils/theme.js`, styled by `.row-action` in `index.css`. Never a raw
  `<Button color="blue">` or `type="primary"` for a row action.
- **Expandable rows**: `<Table components={{ ExpandIcon }} expandable={{ … }}>` (antd 6.7 deprecated
  `expandable.expandIcon`).
- **Downloads**: service call with `responseType: 'blob'`, then `downloadFile(response, fallbackName, message)`
  (file name from Content-Disposition).
- **A page is reachable only** when it's in `permissionRoutes.jsx` **and** `config/menu.jsx`
  (or `extraTitles` for pages not in the menu).
- **API calls** go through `src/services/**`; never import axios in a page. Keep each
  service's header comment in sync with the backend.
- **Errors**: every `catch` → `handleApiError(error, message)`, where `message` comes from
  `App.useApp()`. Never use the static `message` import. In forms, try
  `applyFormErrors(form, error)` first so Laravel's 422 field errors show on the fields.
- **Permissions**: `const { can } = useAuth()`, then `can('user-edit')` (the Administrator
  always passes). `hasPermission` / `hasAnyPermission` don't let the Administrator through
  on their own.
- **Dialogs**: `useModalForm()` (fields empty on every open). Every `<Modal>` / `<Drawer>`
  has `keyboard={false}`, and clicking outside never closes it (lint rule `local/modal-persistent`).
- **AntD 6**: don't trust prop names from v4/v5 memory. `npm run lint` flags props the
  installed antd marks `@deprecated` (`local/antd-deprecated-props`). Known ones: Space/Divider
  `orientation`, Alert `title`, Modal `destroyOnHidden`, Statistic `styles.content`. For
  whole-component deprecations, check the browser console.
- **Server-paged lists**: the store keeps `params` and `fetchItems(changes)` merges and
  reloads (see Users). Small lists load everything and filter or sort client-side (see Roles).
- **Dates**: `formatDate` / `formatDateTime` from `utils/formatDate.js`.
- **Money**: integer cents everywhere; show with `formatMoney`, wrap amounts in `className="money"`
  (tabular figures). Never `toFixed` on floats.
- **Menu sections**: `config/menu.jsx` groups entries with `{ type: 'group' }` (Sell / Back office /
  Settings). `make module` adds to Back office; move the entry if it belongs elsewhere.
- **Touch**: screens cashiers use need ≥ 40px tap targets; `index.css` enlarges row actions and menu
  items on `(pointer: coarse)`.

## Adding a page

**Use the generator** from the project root: `make module name=Product` (frontend half alone:
`node scripts/make-module.mjs Product`). Templates are in `scripts/module-stubs/`; it registers the
pages at the `// [make:module …]` markers in `routes/permissionRoutes.jsx` and `config/menu.jsx`.
Keep those markers. By hand:

1. `src/services/<module>/<x>Api.js`
2. `src/store/<x>Store.js` + `src/hooks/use<X>.js`
3. `src/pages/<module>/<x>/<X>Index.jsx` (+ `<X>FormModal.jsx`, `<X>View.jsx`), copied from Users or Roles
4. `routes/permissionRoutes.jsx` (lazy import) + `config/menu.jsx` (menu entry + `detailRoutes` for the view page)
5. Audited model? add `<AuditHistory record="<Name>" recordId={id} />` to its view page
6. `npm run lint` and `npm run build` are clean
