// src/components/AppProviders.jsx
//
// The app's AntD set-up — theme, persistent dialogs — and its App context
// (message / modal / notification via App.useApp()).

import { App as AntApp, ConfigProvider } from 'antd';
import { antdTheme } from '../utils/theme';

const AppProviders = ({ children }) => (
  <ConfigProvider
    // every paged table offers records per page
    pagination={{ showSizeChanger: true }}
    // every dialog / drawer is persistent: a click outside never closes it —
    // only its close icon, Cancel or its own buttons (Esc: keyboard={false}
    // on each, enforced by the lint rule local/modal-persistent)
    modal={{ mask: { closable: false } }}
    drawer={{ mask: { closable: false } }}
    theme={antdTheme}
  >
    <AntApp>
      {children}
    </AntApp>
  </ConfigProvider>
);

export default AppProviders;
