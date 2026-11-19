// src/pages/register/RegisterPage.jsx
//
// The sell screen, built for a till: products on the left (scan or search box,
// category chips, big tap targets), the sale on the right (lines with − / + steppers,
// totals, Hold / Discount / Void, and a large Pay button).
//
// Scanners type the barcode and press Enter, so Enter in the search box adds an
// exact barcode / SKU match (or the only match) straight to the sale.
// Shortcuts: F2 focus the search · F8 hold · F9 pay.
//
// Products come from GET /register/catalog (active products, categories, store locations); the
// selling location is remembered per browser (one register = one store). Until the Sales module
// exists a completed sale is not saved: the banner says so.

import { useEffect, useMemo, useRef, useState } from 'react';
import { App, Badge, Button, Empty, Input, Popconfirm, Result, Select, Spin, Tooltip } from 'antd';
import {
  BarcodeOutlined, EnvironmentOutlined, DeleteOutlined, ExperimentOutlined, MinusOutlined, PauseCircleOutlined,
  PercentageOutlined, PlusOutlined, ShoppingCartOutlined, StopOutlined, UserOutlined,
} from '@ant-design/icons';
import { COLORS } from '../../utils/theme';
import { formatMoney } from '../../utils/money';
import { VAT } from '../../utils/tax';
import registerApi from '../../services/register/registerApi';
import handleApiError from '../../utils/handleApiError';
import useTicket from './useTicket';
import PaymentModal from './PaymentModal';
import DiscountModal from './DiscountModal';
import HeldSalesDrawer from './HeldSalesDrawer';

// category colours, in order (a strip on each tile and the active chip)
const TONES = [COLORS.info, COLORS.accent, COLORS.success, COLORS.actionDownload, COLORS.navy, COLORS.actionView];

const LOCATION_KEY = 'register_location';
const readLocation = () => {
  try { return Number(localStorage.getItem(LOCATION_KEY)) || null; } catch { return null; }
};

const RegisterPage = () => {
  const { message } = App.useApp();
  const ticket = useTicket(VAT);
  const [catalog, setCatalog] = useState({ state: 'loading', products: [], categories: [], locations: [] });
  const [locationId, setLocationId] = useState(readLocation);
  const { sale, totals } = ticket;
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState('all');
  const [paying, setPaying] = useState(false);
  const [discounting, setDiscounting] = useState(false);
  const [heldOpen, setHeldOpen] = useState(false);
  const searchRef = useRef(null);
  const linesEndRef = useRef(null);

  const loadCatalog = async () => {
    setCatalog((c) => ({ ...c, state: 'loading' }));
    try {
      const { data } = await registerApi.catalog();
      setCatalog({ state: 'ready', ...data });
      // keep the remembered location if it still sells, else the first store
      setLocationId((id) => (data.locations.some((l) => l.id === id) ? id : data.locations[0]?.id ?? null));
    } catch (error) {
      handleApiError(error, message);
      setCatalog((c) => ({ ...c, state: 'error' }));
    }
  };

  useEffect(() => {
    const run = async () => { await loadCatalog(); };
    run();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    try { if (locationId) localStorage.setItem(LOCATION_KEY, locationId); } catch { /* storage blocked */ }
  }, [locationId]);

  const toneOf = useMemo(() => {
    const map = new Map(catalog.categories.map((c, i) => [c.id, TONES[i % TONES.length]]));
    return (categoryId) => map.get(categoryId) ?? COLORS.muted;
  }, [catalog.categories]);

  const products = useMemo(() => {
    const words = query.trim().toLowerCase().split(/\s+/).filter(Boolean);
    return catalog.products.filter((p) => (category === 'all' || p.category_id === category)
      && words.every((w) => `${p.name} ${p.sku} ${p.barcode ?? ''}`.toLowerCase().includes(w)));
  }, [catalog.products, query, category]);

  const focusSearch = () => searchRef.current?.focus();
  const canPay = sale.lines.length > 0;

  const addProduct = (product) => {
    ticket.add(product);
    focusSearch();
  };

  // Enter: an exact barcode / SKU (what a scanner sends), else the only match
  const onSearchEnter = () => {
    const code = query.trim().toLowerCase();
    if (!code) return;
    const exact = catalog.products.find((p) => p.barcode === code || p.sku.toLowerCase() === code);
    const pick = exact ?? (products.length === 1 ? products[0] : null);
    if (pick) {
      ticket.add(pick);
      setQuery('');
    } else {
      message.warning(products.length ? 'Several products match: tap one' : `No product for “${query.trim()}”`);
    }
  };

  const hold = () => {
    if (!canPay) return;
    ticket.hold();
    message.success('Sale held');
    focusSearch();
  };

  // keyboard shortcuts, off while a dialog is open
  useEffect(() => {
    const onKey = (e) => {
      if (paying || discounting || heldOpen) return;
      if (e.key === 'F2') { e.preventDefault(); focusSearch(); }
      if (e.key === 'F8') { e.preventDefault(); hold(); }
      if (e.key === 'F9' && canPay) { e.preventDefault(); setPaying(true); }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  });

  // keep the newest line in view
  useEffect(() => {
    linesEndRef.current?.scrollIntoView({ block: 'nearest' });
  }, [sale.lines.length]);

  const completeSale = ({ change }) => {
    setPaying(false);
    ticket.clear();
    message.success(change > 0 ? `Sale complete: change ${formatMoney(change)} (practice, not saved)` : 'Sale complete (practice, not saved)');
    focusSearch();
  };

  return (
    <div className="reg">
      {/* ── products ─────────────────────────────── */}
      <section className="reg-catalog" aria-label="Products">
        <div className="reg-demo" role="note">
          <ExperimentOutlined /> Practice mode: completed sales are not saved until the Sales module is built.
        </div>

        <div className="reg-toolbar">
          <Select value={locationId} onChange={setLocationId} size="large" className="reg-location" prefix={<EnvironmentOutlined />}
            placeholder="Selling from…" options={catalog.locations.map((l) => ({ value: l.id, label: `${l.code} · ${l.name}` }))}
            notFoundContent="No active store location" aria-label="Selling location" />
          <span className="reg-toolbar__count">{catalog.products.length} products</span>
        </div>

        <Input ref={searchRef} size="large" autoFocus allowClear className="reg-search"
          prefix={<BarcodeOutlined />} suffix={<kbd className="kbd">F2</kbd>}
          placeholder="Scan a barcode or search products" value={query}
          onChange={(e) => setQuery(e.target.value)} onPressEnter={onSearchEnter} aria-label="Scan or search" />

        <div className="reg-cats" role="tablist" aria-label="Categories">
          {[{ id: 'all', name: 'All' }, ...catalog.categories].map((c) => (
            <button key={c.id} type="button" role="tab" aria-selected={category === c.id}
              className={`reg-cat${category === c.id ? ' is-active' : ''}`}
              style={c.id !== 'all' ? { '--tile': toneOf(c.id) } : undefined}
              onClick={() => { setCategory(c.id); focusSearch(); }}>
              {c.name}
            </button>
          ))}
        </div>

        <div className="reg-grid">
          {catalog.state === 'loading' && <Spin className="reg-grid__empty" size="large" />}
          {catalog.state === 'error' && (
            <Result className="reg-grid__empty" status="warning" title="Couldn't load the products"
              extra={<Button type="primary" onClick={loadCatalog}>Try again</Button>} />
          )}
          {catalog.state === 'ready' && products.length === 0 && (
            <Empty className="reg-grid__empty" description={catalog.products.length ? 'No products match' : 'No active products yet: add them in Catalog → Products'} />
          )}
          {products.map((p) => {
            const inSale = sale.lines.find((l) => l.id === p.id)?.qty;
            return (
              <button key={p.id} type="button" className="reg-tile" style={{ '--tile': toneOf(p.category_id) }}
                onClick={() => addProduct(p)} aria-label={`Add ${p.name}`}>
                {inSale > 0 && <span className="reg-tile__qty">{inSale}</span>}
                <span className="reg-tile__name">{p.name}</span>
                <span className="reg-tile__foot">
                  <span className="reg-tile__sku">{p.sku}</span>
                  <span className="reg-tile__price money">{formatMoney(p.price)}</span>
                </span>
              </button>
            );
          })}
        </div>
      </section>

      {/* ── the sale ─────────────────────────────── */}
      <aside className="reg-ticket" aria-label="Current sale">
        <div className="reg-ticket__head">
          <div>
            <div className="reg-ticket__title">Current sale</div>
            <div className="reg-ticket__sub">{totals.items} item{totals.items === 1 ? '' : 's'}</div>
          </div>
          <Tooltip title="Customers arrive with the Customers module">
            <Button icon={<UserOutlined />} disabled>Walk-in</Button>
          </Tooltip>
          <Badge count={ticket.held.length} size="small" color={COLORS.accent}>
            <Button onClick={() => setHeldOpen(true)}>Held</Button>
          </Badge>
        </div>

        <div className="reg-lines">
          {sale.lines.length === 0 ? (
            <div className="reg-lines__empty">
              <ShoppingCartOutlined />
              <strong>No items yet</strong>
              <span>Scan a barcode or tap a product to start a sale.</span>
            </div>
          ) : (
            <ul>
              {sale.lines.map((l) => (
                <li key={l.id} className={`reg-line${ticket.lastAddedId === l.id ? ' is-recent' : ''}`}>
                  <div className="reg-line__info">
                    <span className="reg-line__name">{l.name}</span>
                    <span className="reg-line__unit money">{formatMoney(l.price)} each</span>
                  </div>
                  <div className="reg-stepper">
                    <Button icon={<MinusOutlined />} onClick={() => ticket.setQty(l.id, l.qty - 1)} aria-label={`One less ${l.name}`} />
                    <span className="money">{l.qty}</span>
                    <Button icon={<PlusOutlined />} onClick={() => ticket.setQty(l.id, l.qty + 1)} aria-label={`One more ${l.name}`} />
                  </div>
                  <span className="reg-line__total money">{formatMoney(l.price * l.qty)}</span>
                  <Button type="text" className="reg-line__remove" icon={<DeleteOutlined />} onClick={() => ticket.remove(l.id)} aria-label={`Remove ${l.name}`} />
                </li>
              ))}
              <li ref={linesEndRef} aria-hidden className="reg-lines__end" />
            </ul>
          )}
        </div>

        <div className="reg-totals">
          <div><span>Subtotal</span><span className="money">{formatMoney(totals.subtotal)}</span></div>
          {totals.discount > 0 && (
            <div className="is-discount"><span>Discount {sale.discountPct}%</span><span className="money">−{formatMoney(totals.discount)}</span></div>
          )}
          <div className="is-minor"><span>VATable sales</span><span className="money">{formatMoney(totals.vatableSales)}</span></div>
          <div className={VAT.inclusive ? 'is-minor' : ''}>
            <span>{VAT.label}{VAT.inclusive ? ' (included)' : ''}</span><span className="money">{formatMoney(totals.tax)}</span>
          </div>
          {totals.exemptSales > 0 && (
            <div className="is-minor"><span>VAT-exempt sales</span><span className="money">{formatMoney(totals.exemptSales)}</span></div>
          )}
          {totals.zeroRatedSales > 0 && (
            <div className="is-minor"><span>Zero-rated sales</span><span className="money">{formatMoney(totals.zeroRatedSales)}</span></div>
          )}
          <div className="reg-totals__grand"><span>Total</span><span className="money">{formatMoney(totals.total)}</span></div>
        </div>

        <div className="reg-actions">
          <Button size="large" icon={<PauseCircleOutlined />} disabled={!canPay} onClick={hold}>Hold <kbd className="kbd">F8</kbd></Button>
          <Button size="large" icon={<PercentageOutlined />} disabled={!canPay} onClick={() => setDiscounting(true)}>Discount</Button>
          <Popconfirm title="Void this sale?" description="Every line is removed." okText="Void sale" okButtonProps={{ danger: true }}
            disabled={!canPay} onConfirm={() => { ticket.clear(); focusSearch(); }}>
            <Button size="large" danger icon={<StopOutlined />} disabled={!canPay}>Void</Button>
          </Popconfirm>
        </div>

        <Button type="primary" size="large" block className="reg-pay" disabled={!canPay} onClick={() => setPaying(true)}>
          <span>Pay</span>
          <span className="money">{formatMoney(totals.total)}</span>
          <kbd className="kbd kbd--on-accent">F9</kbd>
        </Button>
      </aside>

      {paying && <PaymentModal total={totals.total} onClose={() => setPaying(false)} onComplete={completeSale} />}
      {discounting && (
        <DiscountModal current={sale.discountPct} onClose={() => setDiscounting(false)}
          onApply={(pct) => { ticket.setDiscount(pct); setDiscounting(false); }} />
      )}
      <HeldSalesDrawer open={heldOpen} held={ticket.held} totalOf={ticket.heldTotal} onClose={() => setHeldOpen(false)}
        onResume={(key) => { ticket.resume(key); setHeldOpen(false); }} onDrop={ticket.drop} />
    </div>
  );
};

export default RegisterPage;
