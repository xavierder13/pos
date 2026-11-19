// src/components/auth/AuthShell.jsx
//
// The layout of every sign-in page (login, forgot / reset password): the
// animated showcase on the left (wide screens) and the panel on the right
// with the brand on top, the page's content centred, the footer at the bottom.

import { SafetyCertificateOutlined } from '@ant-design/icons';
import { APP_NAME } from '../../utils/brand';
import LoginShowcase from './LoginShowcase';
import './login.css';

const AuthShell = ({ eyebrow, title, tagline, children }) => (
  <div className="login-page">
    <LoginShowcase />
    <main className="login-panel">
      <header className="login-panel__top">
        <div className="login-panel__brand">
          <span className="app-logo">{APP_NAME.charAt(0).toUpperCase()}</span>
          <span>{APP_NAME}</span>
        </div>
        <span className="login-panel__secure"><SafetyCertificateOutlined /> Secure sign-in</span>
      </header>

      <section className="login-panel__content">
        {eyebrow && <div className="login-panel__eyebrow">{eyebrow}</div>}
        <h1 className="login-panel__welcome">{title}<span>.</span></h1>
        {tagline && <p className="login-panel__tagline">{tagline}</p>}
        {children}
      </section>

      <footer className="login-panel__footer">
        <span>© {new Date().getFullYear()} {APP_NAME}</span>
        <span>Trouble signing in? Contact your administrator.</span>
      </footer>
    </main>
  </div>
);

export default AuthShell;
