#!/usr/bin/env node
// scripts/make-module.mjs — the frontend half of a module (make module name=Product).
//
//   node scripts/make-module.mjs Product [--force]
//
// Creates src/services/<folder>/<model>Api.js, src/store/<model>Store.js,
// src/hooks/use<Models>.js and src/pages/<folder>/<Model>{Index,FormModal,View}.jsx
// from scripts/module-stubs/, then registers the pages at the `[make:module …]`
// markers in src/routes/permissionRoutes.jsx and src/config/menu.jsx.
// Change the stubs to change every future module. No dependencies (Node only).

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const [rawName, ...flags] = process.argv.slice(2);
const force = flags.includes('--force');

if (!rawName || !/^[A-Za-z][A-Za-z0-9]*$/.test(rawName)) {
  console.error('Usage: node scripts/make-module.mjs <SingularName> (e.g. Product, SalesOrder) [--force]');
  process.exit(1);
}

// ── names ────────────────────────────────────────────────
const singular = (w) => (/ies$/.test(w) ? w.replace(/ies$/, 'y') : /(s|x|z|ch|sh)es$/.test(w) ? w.replace(/es$/, '') : /s$/.test(w) && !/ss$/.test(w) ? w.slice(0, -1) : w);
const plural = (w) => (/[^aeiou]y$/.test(w) ? w.replace(/y$/, 'ies') : /(s|x|z|ch|sh)$/.test(w) ? `${w}es` : `${w}s`);
const words = (s) => s.replace(/([a-z0-9])([A-Z])/g, '$1 $2').split(/\s+/);

const Model = singular(rawName.charAt(0).toUpperCase() + rawName.slice(1));
const Models = plural(Model);
const camel = (s) => s.charAt(0).toLowerCase() + s.slice(1);
const vars = {
  '{{Models}}': Models,
  '{{Model}}': Model,
  '{{models}}': camel(Models),
  '{{model}}': camel(Model),
  '{{kebabs}}': words(Models).join('-').toLowerCase(),
  '{{kebab}}': words(Model).join('-').toLowerCase(),
  '{{folder}}': words(Models).join('_').toLowerCase(),
  '{{CONST}}': words(Models).join('_').toUpperCase(),
  '{{Titles}}': words(Models).join(' '),
  '{{Title}}': words(Model).join(' '),
  '{{titles}}': words(Models).join(' ').toLowerCase(),
  '{{title}}': words(Model).join(' ').toLowerCase(),
};
const render = (stub) => {
  let text = fs.readFileSync(path.join(root, 'scripts/module-stubs', `${stub}.stub`), 'utf8');
  for (const [k, v] of Object.entries(vars)) text = text.split(k).join(v);
  return text;
};

// ── files ────────────────────────────────────────────────
const f = vars['{{folder}}'];
const targets = {
  api: `src/services/${f}/${vars['{{model}}']}Api.js`,
  store: `src/store/${vars['{{model}}']}Store.js`,
  hook: `src/hooks/use${Models}.js`,
  index: `src/pages/${f}/${Model}Index.jsx`,
  form: `src/pages/${f}/${Model}FormModal.jsx`,
  view: `src/pages/${f}/${Model}View.jsx`,
};

if (!force && fs.existsSync(path.join(root, targets.index))) {
  console.error(`${targets.index} already exists. Use --force to overwrite.`);
  process.exit(1);
}

for (const [stub, rel] of Object.entries(targets)) {
  const abs = path.join(root, rel);
  fs.mkdirSync(path.dirname(abs), { recursive: true });
  fs.writeFileSync(abs, render(stub));
  console.log(`  created ${rel}`);
}

// ── registration at the markers ──────────────────────────
const insertAt = (rel, marker, code) => {
  const abs = path.join(root, rel);
  const text = fs.readFileSync(abs, 'utf8');
  const at = text.indexOf(marker);
  if (at < 0) throw new Error(`Marker ${marker} not found in ${rel}`);
  if (text.includes(code.trim().split('\n')[0])) return;
  const lineStart = text.lastIndexOf('\n', at) + 1;
  fs.writeFileSync(abs, text.slice(0, lineStart) + code + text.slice(lineStart));
  console.log(`  updated ${rel}`);
};

const k = vars['{{kebab}}'];
const ks = vars['{{kebabs}}'];
insertAt('src/routes/permissionRoutes.jsx', '// [make:module imports]',
  `const ${Model}Index = lazy(() => import('../pages/${f}/${Model}Index'));\nconst ${Model}View = lazy(() => import('../pages/${f}/${Model}View'));\n`);
insertAt('src/routes/permissionRoutes.jsx', '// [make:module routes]',
  `  { permissions: ['${k}-list'], path: '/${ks}', element: <${Model}Index /> },\n  { permissions: ['${k}-list', '${k}-edit'], path: '/${ks}/:id', element: <${Model}View /> },\n`);
// the menu entry's icon import (once)
const menuPath = path.join(root, 'src/config/menu.jsx');
const menuText = fs.readFileSync(menuPath, 'utf8');
if (!/\bAppstoreOutlined\b/.test(menuText)) {
  fs.writeFileSync(menuPath, menuText.replace(/import \{\s*/, (m) => `${m}AppstoreOutlined, `));
}
insertAt('src/config/menu.jsx', '// [make:module menu]',
  `  { key: '${ks}', label: '${vars['{{Titles}}']}', icon: <AppstoreOutlined />, path: '/${ks}', permissions: ['${k}-list'] },\n`);
insertAt('src/config/menu.jsx', '// [make:module detail]',
  `  { pattern: /^\\/${ks}\\/\\d+$/, parent: '/${ks}', label: 'View ${vars['{{Title}}']}' },\n`);

console.log(`\n${vars['{{Titles}}']} pages created: /${ks} and /${ks}/:id (menu entry added; change its icon in src/config/menu.jsx).`);
