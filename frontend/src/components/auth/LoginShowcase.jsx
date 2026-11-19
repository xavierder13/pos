// src/components/auth/LoginShowcase.jsx
//
// Animated side panel of the login page: a glowing accent grid, a small
// dashboard window with growing bars and a line chart, floating cards, and
// rotating captions. Pure CSS / SVG animation (login.css), no image files;
// motion stops under prefers-reduced-motion. Decorative only, hidden on small
// screens. Replace the captions and cards with your project's own story.

import { useEffect, useState } from 'react';
import { APP_NAME } from '../../utils/brand';

const CAPTIONS = [
  { title: 'Everything in one place', text: 'Your team, your data and your workflows — in one fast, secure workspace.' },
  { title: 'Access you control', text: 'Roles and permissions decide exactly who sees and does what.' },
  { title: 'Every change on record', text: 'The audit trail keeps who changed what, and when.' },
];

const BARS = [38, 62, 48, 80, 58, 92, 70];
const CAPTION_MS = 5000;

const LoginShowcase = () => {
  const [caption, setCaption] = useState(0);

  useEffect(() => {
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return undefined;
    const id = setInterval(() => setCaption((i) => (i + 1) % CAPTIONS.length), CAPTION_MS);
    return () => clearInterval(id);
  }, [caption]);

  return (
    <aside className="login-showcase">
      <div className="login-showcase__grid" aria-hidden="true" />
      <div className="login-showcase__glow login-showcase__glow--a" aria-hidden="true" />
      <div className="login-showcase__glow login-showcase__glow--b" aria-hidden="true" />

      <div className="login-showcase__brand">
        <span className="app-logo">{APP_NAME.charAt(0).toUpperCase()}</span>
        <span>{APP_NAME}</span>
      </div>

      <div className="login-scene" aria-hidden="true">
        <div className="login-scene__window">
          <div className="login-scene__title"><i /><i /><i /> Overview</div>
          <div className="login-scene__body">
            <div className="login-scene__bars">
              {BARS.map((h, i) => <span key={i} style={{ '--h': `${h}%`, '--d': `${i * 0.12}s` }} />)}
            </div>
            <svg className="login-scene__line" viewBox="0 0 280 100" preserveAspectRatio="none">
              <polyline points="0,72 46,50 93,60 140,32 186,44 233,16 280,24" />
            </svg>
          </div>
        </div>

        <div className="login-card login-card--a">
          <span className="login-card__dot" />
          <b>12 active users</b>
          <small>3 roles · 10 permissions</small>
        </div>
        <div className="login-card login-card--b">
          <span className="login-card__dot login-card__dot--blue" />
          <b>Role updated</b>
          <small>Audit trail · just now</small>
        </div>
        <div className="login-card login-card--c">
          <span className="login-card__dot login-card__dot--green" />
          <b>All systems normal</b>
          <small>API · Database · Queue</small>
        </div>
      </div>

      <div className="login-showcase__caption">
        <h2 key={caption}>{CAPTIONS[caption].title}</h2>
        <p key={`t${caption}`}>{CAPTIONS[caption].text}</p>
        <div className="login-showcase__dots">
          {CAPTIONS.map((c, i) => (
            <button key={c.title} type="button" aria-label={c.title}
              className={i === caption ? 'is-active' : undefined} onClick={() => setCaption(i)} />
          ))}
        </div>
      </div>
    </aside>
  );
};

export default LoginShowcase;
