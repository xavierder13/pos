import js from '@eslint/js'
import globals from 'globals'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import { defineConfig, globalIgnores } from 'eslint/config'
import antdDeprecatedProps from './eslint-rules/antd-deprecated-props.js'
import modalPersistent from './eslint-rules/modal-persistent.js'

export default defineConfig([
  globalIgnores(['dist']),
  {
    files: ['**/*.{js,jsx}'],
    extends: [
      js.configs.recommended,
      reactHooks.configs.flat.recommended,
      reactRefresh.configs.vite,
    ],
    languageOptions: {
      globals: globals.browser,
      parserOptions: { ecmaFeatures: { jsx: true } },
    },
    // Props the installed antd marks @deprecated (they only warn in the
    // browser console otherwise); every Modal / Drawer persistent.
    plugins: { local: { rules: { 'antd-deprecated-props': antdDeprecatedProps, 'modal-persistent': modalPersistent } } },
    rules: { 'local/antd-deprecated-props': 'error', 'local/modal-persistent': 'error' },
  },
  // route / menu tables: data, not components (Fast Refresh reloads them fully)
  {
    files: ['src/routes/permissionRoutes.jsx', 'src/config/**'],
    rules: { 'react-refresh/only-export-components': 'off' },
  },
  {
    files: ['eslint-rules/**', '*.config.js'],
    languageOptions: { globals: globals.node },
  },
])
