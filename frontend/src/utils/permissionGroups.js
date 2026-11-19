// src/utils/permissionGroups.js
//
// Permission names are `<module>-<action>`. These split and group them for
// the Roles & Permissions viewers and the role form.

// "audit-trail-list" → { module: 'audit-trail', action: 'list' }: the action
// starts at the first action word after the module, so a several-word action
// stays whole ("report-export-all" → report / export-all). Without one, the
// last word is the action. A one-word permission is its own module.
const ACTION_WORDS = ['list', 'create', 'edit', 'delete', 'view', 'assign', 'approve', 'export', 'print', 'sync', 'all'];

export const splitPermission = (name) => {
  const parts = name.split('-');
  const at = parts.findIndex((w, i) => i > 0 && ACTION_WORDS.includes(w));
  if (at > 0) return { module: parts.slice(0, at).join('-'), action: parts.slice(at).join('-') };
  if (parts.length === 1) return { module: name, action: '' };
  return { module: parts.slice(0, -1).join('-'), action: parts.at(-1) };
};

export const moduleLabel = (module) => module.split('-').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

// [{ module, label, permissions: [{ ...permission, action }] }] sorted by label
export const groupByModule = (permissions) => {
  const groups = {};
  permissions.forEach((p) => {
    const { module, action } = splitPermission(p.name);
    (groups[module] ||= { module, label: moduleLabel(module), permissions: [] }).permissions.push({ ...p, action });
  });
  return Object.values(groups)
    .map((g) => ({ ...g, permissions: g.permissions.sort((a, b) => a.name.localeCompare(b.name)) }))
    .sort((a, b) => a.label.localeCompare(b.label));
};

export const plural = (n, word) => `${n} ${word}${n === 1 ? '' : 's'}`;
