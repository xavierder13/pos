// src/pages/register/useTicket.js
//
// The sale on the register screen: lines, a sale-level % discount, held (parked)
// sales, and the totals. All money is integer minor units (centavos): discount and
// tax are rounded once, on the sale, so the lines always add up to the total.
// Each line carries its product's tax_class (vatable | exempt | zero_rated). The sale discount is
// spread over the classes by value (remainder on exempt), then VAT is taken from the vatable part:
// tax = { rate, inclusive }; inclusive (PH VAT) means prices already contain it, so
// VAT = vatable × rate / (1 + rate) and the total is not increased. The breakdown matches a PH
// invoice: VATable sales (net of VAT), VAT, VAT-exempt sales, zero-rated sales.

import { useCallback, useMemo, useReducer } from 'react';

const emptySale = () => ({ lines: [], discountPct: 0, note: '' });

const initial = { sale: emptySale(), held: [] };

const reducer = (state, action) => {
  const { sale } = state;
  switch (action.type) {
    case 'add': {
      const { product, qty = 1 } = action;
      const found = sale.lines.find((l) => l.id === product.id);
      const lines = found
        ? sale.lines.map((l) => (l.id === product.id ? { ...l, qty: l.qty + qty } : l))
        : [...sale.lines, { id: product.id, sku: product.sku, name: product.name, price: product.price, taxClass: product.tax_class ?? 'vatable', qty }];
      return { ...state, sale: { ...sale, lines }, lastAddedId: product.id };
    }
    case 'qty': {
      const lines = sale.lines
        .map((l) => (l.id === action.id ? { ...l, qty: Math.max(0, action.qty) } : l))
        .filter((l) => l.qty > 0);
      return { ...state, sale: { ...sale, lines } };
    }
    case 'remove':
      return { ...state, sale: { ...sale, lines: sale.lines.filter((l) => l.id !== action.id) } };
    case 'discount':
      return { ...state, sale: { ...sale, discountPct: Math.min(100, Math.max(0, action.pct)) } };
    case 'clear':
      return { ...state, sale: emptySale() };
    case 'hold':
      if (!sale.lines.length) return state;
      return {
        ...state,
        sale: emptySale(),
        held: [{ ...sale, key: Date.now(), heldAt: new Date().toISOString(), label: action.label }, ...state.held],
      };
    case 'resume': {
      const target = state.held.find((h) => h.key === action.key);
      if (!target) return state;
      // the sale on screen (if any) is parked in its place
      const rest = state.held.filter((h) => h.key !== action.key);
      const parked = sale.lines.length ? [{ ...sale, key: Date.now(), heldAt: new Date().toISOString() }] : [];
      return { ...state, sale: { lines: target.lines, discountPct: target.discountPct, note: target.note }, held: [...parked, ...rest] };
    }
    case 'drop':
      return { ...state, held: state.held.filter((h) => h.key !== action.key) };
    default:
      return state;
  }
};

const gross = (lines, cls) => lines.reduce((sum, l) => sum + (l.taxClass === cls ? l.price * l.qty : 0), 0);

export const totalsOf = (sale, { rate, inclusive }) => {
  const subtotal = sale.lines.reduce((sum, l) => sum + l.price * l.qty, 0);
  const discount = Math.round((subtotal * sale.discountPct) / 100);
  const share = (part) => (subtotal ? Math.round((discount * part) / subtotal) : 0);

  const vatableGross = gross(sale.lines, 'vatable');
  const zeroGross = gross(sale.lines, 'zero_rated');
  const exemptGross = subtotal - vatableGross - zeroGross;
  const vatableDiscount = share(vatableGross);
  const zeroDiscount = share(zeroGross);
  const exemptDiscount = discount - vatableDiscount - zeroDiscount;

  const vatableAmount = vatableGross - vatableDiscount;
  const tax = inclusive ? Math.round((vatableAmount * rate) / (1 + rate)) : Math.round(vatableAmount * rate);
  const total = subtotal - discount + (inclusive ? 0 : tax);
  const items = sale.lines.reduce((sum, l) => sum + l.qty, 0);

  return {
    subtotal,
    discount,
    tax,
    vatableSales: inclusive ? vatableAmount - tax : vatableAmount,   // net of VAT
    exemptSales: exemptGross - exemptDiscount,
    zeroRatedSales: zeroGross - zeroDiscount,
    total,
    items,
  };
};

const useTicket = (tax) => {
  const [state, dispatch] = useReducer(reducer, initial);
  const totals = useMemo(() => totalsOf(state.sale, tax), [state.sale, tax]);

  const actions = useMemo(() => ({
    add: (product, qty) => dispatch({ type: 'add', product, qty }),
    setQty: (id, qty) => dispatch({ type: 'qty', id, qty }),
    remove: (id) => dispatch({ type: 'remove', id }),
    setDiscount: (pct) => dispatch({ type: 'discount', pct }),
    clear: () => dispatch({ type: 'clear' }),
    hold: (label) => dispatch({ type: 'hold', label }),
    resume: (key) => dispatch({ type: 'resume', key }),
    drop: (key) => dispatch({ type: 'drop', key }),
  }), []);

  const heldTotal = useCallback((h) => totalsOf(h, tax).total, [tax]);

  return { sale: state.sale, held: state.held, lastAddedId: state.lastAddedId, totals, heldTotal, ...actions };
};

export default useTicket;
