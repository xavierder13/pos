// Signs in, then screenshots each path in PATHS at desktop and tablet size; prints console errors.
const puppeteer = require('puppeteer');

const SIZES = { desktop: { width: 1440, height: 900 }, tablet: { width: 820, height: 1180, isMobile: true, hasTouch: true } };
const wait = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const errors = [];
  page.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
  page.on('pageerror', (e) => errors.push(e.message));

  await page.setViewport(SIZES.desktop);
  await page.goto(`${process.env.BASE}/login`, { waitUntil: 'networkidle0' });
  await page.type('input[type=email], input#email, input[name=email]', 'admin@example.com');
  await page.type('input[type=password]', process.env.PW);
  await page.keyboard.press('Enter');
  await page.waitForNavigation({ waitUntil: 'networkidle0' }).catch(() => {});

  for (const path of process.env.PATHS.split(/\s+/).filter(Boolean)) {
    for (const [size, viewport] of Object.entries(SIZES)) {
      await page.setViewport(viewport);
      await page.goto(`${process.env.BASE}${path}`, { waitUntil: 'networkidle0' });
      await wait(800);
      const file = `${(path.replace(/\W+/g, '-').replace(/^-|-$/g, '') || 'home')}-${size}.png`;
      await page.screenshot({ path: `/out/${file}`, fullPage: size === 'tablet' });
      console.log(`shot ${path} (${size}) → ${file}`);
    }
  }
  console.log(errors.length ? `console errors:\n${[...new Set(errors)].join('\n')}` : 'console errors: none');
  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
