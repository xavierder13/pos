// src/utils/money.js
//
// Money on screen. Amounts are integers in the currency's minor unit (cents),
// never floats: 1999 → "₱19.99". Currency and locale come from .env
// (VITE_CURRENCY, VITE_LOCALE) until the store settings module owns them.

export const CURRENCY = import.meta.env.VITE_CURRENCY || 'PHP';
export const LOCALE = import.meta.env.VITE_LOCALE || 'en-PH';

const formatter = new Intl.NumberFormat(LOCALE, { style: 'currency', currency: CURRENCY });

// digits after the decimal point for this currency (2 for USD, 0 for JPY)
export const MINOR_DIGITS = formatter.resolvedOptions().maximumFractionDigits;
const FACTOR = 10 ** MINOR_DIGITS;

export const formatMoney = (minor = 0) => formatter.format(minor / FACTOR);

// the currency symbol alone ("₱", "$", "€") for input prefixes
export const CURRENCY_SYMBOL = formatter.formatToParts(0).find((p) => p.type === 'currency')?.value ?? CURRENCY;

// a typed amount (19.99) → minor units (1999), rounded half away from zero
export const toMinor = (major) => Math.round((Number(major) || 0) * FACTOR);
export const toMajor = (minor) => (minor || 0) / FACTOR;
