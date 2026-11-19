// Kahero POS site: mobile nav, sticky-header shadow, industry tabs, billing toggle,
// scroll reveal, and the demo form (validated here; wire `submit` to your CRM or an API endpoint).

(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];

  // header: shadow once scrolled, mobile menu
  const nav = $('[data-nav]');
  const toggle = $('[data-nav-toggle]');
  const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(open));
  });
  $$('.nav__links a, .nav__cta a').forEach((a) => a.addEventListener('click', () => {
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }));

  // industry tabs (arrow keys move between tabs)
  const tabs = $$('[role="tab"]');
  const select = (tab) => {
    tabs.forEach((t) => {
      const on = t === tab;
      t.setAttribute('aria-selected', String(on));
      t.tabIndex = on ? 0 : -1;
      document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
    });
  };
  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => select(tab));
    tab.addEventListener('keydown', (e) => {
      const step = { ArrowRight: 1, ArrowLeft: -1 }[e.key];
      if (!step) return;
      const next = tabs[(i + step + tabs.length) % tabs.length];
      select(next);
      next.focus();
    });
  });

  // pricing: monthly / yearly
  $$('[data-billing]').forEach((btn) => btn.addEventListener('click', () => {
    const mode = btn.dataset.billing;
    $$('[data-billing]').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
    $$('[data-monthly]').forEach((el) => { el.textContent = el.dataset[mode]; });
  }));

  // reveal sections as they scroll in
  const targets = $$('.feature, .show__copy, .show__media, .tabs, .band__list li, .plan, .faq__list details');
  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    targets.forEach((el) => el.classList.add('reveal'));
    const io = new IntersectionObserver((entries) => entries.forEach((e) => {
      if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
    }), { rootMargin: '0px 0px -8% 0px' });
    targets.forEach((el) => io.observe(el));
  }

  // demo form: client-side checks, then a thank-you (no backend in this sample)
  const form = $('[data-demo-form]');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    let first = null;
    $$('input, select, textarea', form).forEach((field) => {
      const bad = !field.checkValidity();
      field.classList.toggle('is-invalid', bad);
      if (bad && !first) first = field;
    });
    if (first) { first.focus(); return; }
    $('.form__done', form).hidden = false;
  });
  form.addEventListener('input', (e) => e.target.classList.remove('is-invalid'));

  $('[data-year]').textContent = new Date().getFullYear();
})();
