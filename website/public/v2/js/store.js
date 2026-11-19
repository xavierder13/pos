// Suki Corner: fills the page from the POS's public storefront endpoint (prices, menu, stock buckets).
// Live first (<meta name="storefront-api">); if the API can't be reached, the saved snapshot in
// data/storefront.json, labelled as such. Prices are integer centavos, VAT-inclusive.

(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];

  // ── opening hours (Asia/Manila) ─────────────────────────
  const HOURS = [ // 0 = Sunday
    { day: 'Sunday', open: '08:00', close: '20:00' },
    { day: 'Monday', open: '06:30', close: '21:00' },
    { day: 'Tuesday', open: '06:30', close: '21:00' },
    { day: 'Wednesday', open: '06:30', close: '21:00' },
    { day: 'Thursday', open: '06:30', close: '21:00' },
    { day: 'Friday', open: '06:30', close: '22:00' },
    { day: 'Saturday', open: '07:00', close: '22:00' },
  ];

  const manilaNow = () => {
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-US', {
      timeZone: 'Asia/Manila', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date()).map((p) => [p.type, p.value]));
    const day = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(parts.weekday);
    return { day, minutes: Number(parts.hour) * 60 + Number(parts.minute) };
  };
  const toMin = (hhmm) => { const [h, m] = hhmm.split(':').map(Number); return h * 60 + m; };
  const label12 = (hhmm) => {
    const [h, m] = hhmm.split(':').map(Number);
    return `${((h + 11) % 12) + 1}${m ? `:${String(m).padStart(2, '0')}` : ''} ${h < 12 ? 'AM' : 'PM'}`;
  };

  const renderHours = () => {
    const { day, minutes } = manilaNow();
    $('[data-hours]').innerHTML = HOURS.map((h, i) => `
      <tr class="${i === day ? 'is-today' : ''}"><td>${h.day}</td><td>${label12(h.open)} – ${label12(h.close)}</td></tr>`).join('');
    const today = HOURS[day];
    const open = minutes >= toMin(today.open) && minutes < toMin(today.close);
    const status = $('[data-open-status]');
    status.classList.toggle('is-open', open);
    status.classList.toggle('is-closed', !open);
    let text;
    if (open) {
      text = `Open now · until ${label12(today.close)}`;
    } else if (minutes < toMin(today.open)) {
      text = `Closed · opens today at ${label12(today.open)}`;
    } else {
      text = `Closed · opens tomorrow at ${label12(HOURS[(day + 1) % 7].open)}`;
    }
    status.lastElementChild.textContent = text;
  };

  // ── formatting ──────────────────────────────────────────
  const peso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 0, maximumFractionDigits: 2 });
  const money = (centavos) => peso.format(centavos / 100);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const AVAIL = { in_stock: 'In stock', few_left: 'Few left', sold_out: 'Sold out', available: 'Available' };
  const pill = (a) => `<span class="avail avail--${a}">${AVAIL[a] ?? a}</span>`;

  // a picture for each item until real product photos exist
  const EMOJI = [
    [/americano|coffee|kape/i, '☕'], [/\blatte\b/i, '☕'], [/iced tea/i, '🧋'], [/water/i, '💧'],
    [/ensaymada/i, '🥐'], [/adobo|rice meal/i, '🍛'], [/pandesal/i, '🥖'], [/pancit|canton|noodle/i, '🍜'],
    [/milk/i, '🥛'], [/egg/i, '🥚'], [/bread|loaf/i, '🍞'], [/rice/i, '🍚'],
    [/soap|dish/i, '🧼'], [/towel|tissue/i, '🧻'], [/batter/i, '🔋'], [/gift|wrap/i, '🎁'], [/deliver/i, '🛵'],
  ];
  const CATEGORY_EMOJI = { Drinks: '🥤', Food: '🍽️', Grocery: '🛒', Household: '🏠', Services: '🤝' };
  const TINT = { Drinks: '#e2eef7', Food: '#fbe7dc', Grocery: '#e3f0e3', Household: '#f1ebdd', Services: '#ece6f5' };
  const emojiFor = (p, cat) => (EMOJI.find(([re]) => re.test(p.name))?.[1]) ?? CATEGORY_EMOJI[cat] ?? '🛍️';

  // ── render ──────────────────────────────────────────────
  let shelfItems = [];

  const render = (data, live) => {
    const cats = Object.fromEntries(data.categories.map((c) => [c.id, c.name]));
    const products = data.products.map((p) => ({ ...p, category: cats[p.category_id] ?? 'Other' }));
    const byName = Object.fromEntries(products.map((p) => [p.name, p]));
    const inCat = (name) => products.filter((p) => p.category === name);

    if (data.store) {
      $$('[data-store-name]').forEach((el) => { el.textContent = data.store.name; });
      $$('[data-store-address]').forEach((el) => { el.textContent = data.store.address || ''; });
      $('[data-directions]').href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(data.store.address || data.store.name)}`;
    }
    $$('[data-price-of]').forEach((el) => {
      const p = byName[el.dataset.priceOf];
      el.textContent = p ? money(p.price) : '—';
    });
    $$('[data-price-from]').forEach((el) => {
      const prices = inCat(el.dataset.priceFrom).filter((p) => p.availability === 'available').map((p) => p.price);
      el.textContent = prices.length ? money(Math.min(...prices)) : '—';
    });
    $$('[data-avail-of]').forEach((el) => {
      const p = byName[el.dataset.availOf];
      el.outerHTML = p ? pill(p.availability) : '';
    });

    // café menu: everything in Drinks and Food
    $$('[data-category]').forEach((ul) => {
      ul.innerHTML = inCat(ul.dataset.category).map((p) => `
        <li class="menu__item${p.availability === 'sold_out' ? ' is-out' : ''}">
          <span class="menu__emoji" aria-hidden="true">${emojiFor(p, p.category)}</span>
          <span class="menu__name">${esc(p.name)}</span>
          <span class="menu__price">${money(p.price)}</span>
          <span class="menu__meta">
            ${p.availability === 'available' ? 'Made to order' : pill(p.availability)}
            ${p.sc_pwd_eligible ? '<span class="leaf" title="Senior / PWD discount applies">🍃</span>' : ''}
          </span>
        </li>`).join('') || '<li class="muted">Nothing on the menu right now.</li>';
    });

    // grocery shelf: stocked items (grocery, household, bottled drinks)
    shelfItems = products.filter((p) => p.availability !== 'available' && p.category !== 'Food');
    renderShelf($('[data-filter][aria-pressed="true"]')?.dataset.filter ?? 'all');

    // services
    $('[data-services]').innerHTML = inCat('Services').map((p) => `
      <article class="service">
        <span class="service__emoji" aria-hidden="true">${emojiFor(p, 'Services')}</span>
        <div><h3>${esc(p.name)}</h3><p>${/deliver/i.test(p.name) ? 'To your door, same day' : 'Ready while you wait'}</p></div>
        <span class="service__price">${money(p.price)}</span>
      </article>`).join('');

    const when = new Date(data.generated_at);
    const time = when.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', timeZone: 'Asia/Manila' });
    $$('[data-updated]').forEach((el) => { el.textContent = live ? time : when.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }); });
    $('.live').classList.toggle('is-offline', !live);
    $('[data-live-note]').textContent = live
      ? `Live from our store counter · prices and stock updated ${time}`
      : `Showing our last saved prices (${when.toLocaleDateString('en-PH', { month: 'long', day: 'numeric' })}). Call us to check stock.`;
  };

  const renderShelf = (filter) => {
    const list = filter === 'all' ? shelfItems : shelfItems.filter((p) => p.category === filter);
    $('[data-shelf]').innerHTML = list.map((p) => `
      <article class="item${p.availability === 'sold_out' ? ' is-out' : ''}">
        <div class="item__art" style="--tint:${TINT[p.category] ?? '#f4ebdc'}" aria-hidden="true">${emojiFor(p, p.category)}</div>
        <div class="item__name">${esc(p.name)} ${p.sc_pwd_eligible ? '<span class="leaf" title="Senior / PWD discount applies">🍃</span>' : ''}</div>
        <div class="item__foot"><span class="item__price">${money(p.price)}</span>${pill(p.availability)}</div>
      </article>`).join('') || '<p class="muted">Nothing here right now.</p>';
  };

  $$('[data-filter]').forEach((btn) => btn.addEventListener('click', () => {
    $$('[data-filter]').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
    renderShelf(btn.dataset.filter);
  }));

  // ── load: live, else the snapshot ───────────────────────
  const load = async () => {
    $('[data-shelf]').innerHTML = '<div class="skeleton"></div>'.repeat(4);
    const api = document.querySelector('meta[name="storefront-api"]')?.content;
    try {
      const ctrl = new AbortController();
      const timer = setTimeout(() => ctrl.abort(), 5000);
      const res = await fetch(api, { signal: ctrl.signal, headers: { Accept: 'application/json' } });
      clearTimeout(timer);
      if (!res.ok) throw new Error(res.status);
      render(await res.json(), true);
    } catch {
      const res = await fetch('data/storefront.json');
      render(await res.json(), false);
    }
  };

  // ── header ──────────────────────────────────────────────
  const nav = $('[data-nav]');
  const toggle = $('[data-nav-toggle]');
  window.addEventListener('scroll', () => nav.classList.toggle('is-scrolled', window.scrollY > 8), { passive: true });
  toggle.addEventListener('click', () => toggle.setAttribute('aria-expanded', String(nav.classList.toggle('is-open'))));
  $$('.nav__links a').forEach((a) => a.addEventListener('click', () => { nav.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); }));

  renderHours();
  setInterval(renderHours, 60_000);
  load();
})();
