// src/utils/theme.js
//
// The app's look: a LIGHT theme from the reference palette
//   navy #172A39 · warm off-white #E9E4E0 · coral #FC563C · slate grey #6E7575
// Warm off-white pages and light cards with navy text, a coral accent, and two
// navy "brand" panels: the sidebar and the login page's showcase.
//
// COLORS is the single source: AntD reads it through `antdTheme`, and the CSS
// (index.css, login.css) reads the same values as `--<name>` variables, which
// applyCssVars() writes onto :root before the app renders. Change a colour here only.

import { theme } from 'antd';

export const COLORS = {
  // accent: coral
  accent: '#fc563c',          // primary
  accentHover: '#ff7a63',
  accentActive: '#e03e25',
  accent2: '#e03e25',         // the deep end of the accent gradient (logo, avatars)
  accentSoft: 'rgba(252, 86, 60, 0.12)',
  accentFaint: 'rgba(252, 86, 60, 0.05)',
  accentGlow: 'rgba(252, 86, 60, 0.30)',
  onAccent: '#ffffff',        // text on a coral fill

  // light layers. (`black` keeps its name across the theme copies: it is the page
  // background, here the warm off-white.)
  black: '#e9e4e0',         // page background
  surface: '#f8f6f4',       // cards, tables, header
  surface2: '#f0ece8',      // table headers, tags, hover rows
  elevated: '#ffffff',      // modals, dropdowns, popovers, inputs
  border: '#d8d1cb',
  borderSoft: '#e6e0db',

  // text: navy, with the reference slate grey for secondary text
  text: '#172a39',
  muted: '#6e7575',
  faint: '#9aa0a0',

  // the navy brand panels (sidebar, login showcase) and the text on them
  navy: '#172a39',
  navy2: '#1f3547',         // a raised navy (cards on the showcase, sub-menus)
  navyBorder: '#2b4356',
  onNavy: '#e9e4e0',
  onNavyMuted: '#9fb0bd',
  accentOnNavy: '#ff8a75',  // coral lifted a step so it reads on navy

  // status, tuned for a light page: error is a deeper red than the coral accent
  success: '#1f9d63',
  warning: '#c98a00',
  error: '#d0303a',
  info: '#2f6fb0',

  // row actions (components/ActionButton): tonal icon buttons drawn from the palette,
  // each action its own hue so they stay told apart at a glance
  actionView: '#5f6868',    // slate: look, no change
  actionEdit: '#e03e25',    // deep coral (the accent): change this record
  actionAssign: '#172a39',  // navy: change who it belongs to
  actionDownload: '#9a6d12', // dark gold: take a copy out (download, print, export)
  actionConfirm: '#1f9d63', // green: approve / mark done
  actionDelete: '#d0303a',  // red, fills solid on hover
};

// COLORS → CSS variables: accentSoft → --accent-soft
export const applyCssVars = (root = document.documentElement) => {
  Object.entries(COLORS).forEach(([name, value]) => {
    root.style.setProperty(`--${name.replace(/([a-z])([A-Z0-9])/g, '$1-$2').toLowerCase()}`, value);
  });
};

// dropdowns / popovers: a hairline border + a soft navy shadow
const floating = `0 0 0 1px ${COLORS.borderSoft}, 0 12px 32px -8px rgba(23, 42, 57, 0.18)`;

export const antdTheme = {
  algorithm: theme.defaultAlgorithm,
  token: {
    colorPrimary: COLORS.accent,
    colorInfo: COLORS.info,
    colorSuccess: COLORS.success,
    colorWarning: COLORS.warning,
    colorError: COLORS.error,
    colorLink: COLORS.accentActive,      // the deeper coral reads better as text on light
    colorLinkHover: COLORS.accent,
    colorLinkActive: COLORS.accentActive,
    colorBgBase: COLORS.elevated,
    colorBgLayout: COLORS.black,
    colorBgContainer: COLORS.surface,
    colorBgElevated: COLORS.elevated,
    colorBgSpotlight: COLORS.navy,       // tooltips: navy with light text
    colorBorder: COLORS.border,
    colorBorderSecondary: COLORS.borderSoft,
    colorSplit: COLORS.borderSoft,
    colorText: COLORS.text,
    colorTextBase: COLORS.text,
    colorTextSecondary: COLORS.muted,
    colorTextTertiary: COLORS.faint,
    colorTextPlaceholder: COLORS.faint,
    controlItemBgActive: COLORS.accentSoft,
    controlItemBgActiveHover: 'rgba(252, 86, 60, 0.18)',
    controlOutline: 'rgba(252, 86, 60, 0.20)',
    boxShadowSecondary: floating,
    borderRadius: 10,
    fontFamily: "'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif",
  },
  components: {
    Button: {
      primaryColor: COLORS.onAccent,
      primaryShadow: `0 6px 18px -8px ${COLORS.accentGlow}`,
      defaultBg: COLORS.elevated,
      defaultBorderColor: COLORS.border,
      defaultColor: COLORS.text,
      fontWeight: 600,
    },
    Layout: {
      bodyBg: COLORS.black,
      headerBg: 'rgba(233, 228, 224, 0.82)',
      headerPadding: '0 20px',
      siderBg: COLORS.navy,
    },
    // the sidebar menu is AntD's "dark" menu, painted navy
    Menu: {
      darkItemBg: COLORS.navy,
      darkSubMenuItemBg: '#132431',
      darkPopupBg: COLORS.navy,
      darkItemSelectedBg: 'rgba(252, 86, 60, 0.18)',
      darkItemSelectedColor: COLORS.accentOnNavy,
      darkItemHoverBg: 'rgba(255, 255, 255, 0.06)',
      darkItemHoverColor: COLORS.onNavy,
      darkItemColor: '#b9c5cf',
      itemBorderRadius: 8,
      itemMarginInline: 8,
    },
    Input: {
      activeBorderColor: COLORS.accent,
      hoverBorderColor: 'rgba(252, 86, 60, 0.55)',
      activeShadow: `0 0 0 3px ${COLORS.accentSoft}`,
      activeBg: COLORS.elevated,
      hoverBg: COLORS.elevated,
    },
    InputNumber: {
      activeBorderColor: COLORS.accent,
      hoverBorderColor: 'rgba(252, 86, 60, 0.55)',
      activeShadow: `0 0 0 3px ${COLORS.accentSoft}`,
    },
    Select: {
      activeBorderColor: COLORS.accent,
      hoverBorderColor: 'rgba(252, 86, 60, 0.55)',
      activeOutlineColor: COLORS.accentSoft,
      optionSelectedBg: COLORS.accentSoft,
      optionSelectedColor: COLORS.accentActive,
      optionActiveBg: COLORS.surface2,
    },
    DatePicker: {
      activeBorderColor: COLORS.accent,
      hoverBorderColor: 'rgba(252, 86, 60, 0.55)',
      activeShadow: `0 0 0 3px ${COLORS.accentSoft}`,
    },
    Table: {
      headerBg: COLORS.surface2,
      headerColor: COLORS.muted,
      headerSplitColor: 'transparent',
      rowHoverBg: COLORS.accentFaint,
      rowSelectedBg: COLORS.accentSoft,
      rowSelectedHoverBg: 'rgba(252, 86, 60, 0.16)',
      borderColor: COLORS.borderSoft,
    },
    Card: { colorBorderSecondary: COLORS.border, headerBg: 'transparent' },
    Modal: { contentBg: COLORS.elevated, headerBg: COLORS.elevated, footerBg: COLORS.elevated },
    Drawer: { colorBgElevated: COLORS.elevated },
    Tabs: {
      inkBarColor: COLORS.accent,
      itemSelectedColor: COLORS.accentActive,
      itemHoverColor: COLORS.accent,
      itemColor: COLORS.muted,
    },
    Pagination: {
      itemActiveBg: COLORS.accentSoft,
      itemActiveColor: COLORS.accentActive,
      itemActiveColorHover: COLORS.accent,
    },
    Tag: { defaultBg: COLORS.surface2, defaultColor: COLORS.text },
    Segmented: { itemSelectedBg: COLORS.elevated, itemSelectedColor: COLORS.accentActive, trackBg: COLORS.surface2 },
    Switch: { handleBg: '#ffffff' },
    Badge: { dotSize: 8 },
  },
};
