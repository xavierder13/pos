// src/utils/tax.js — Philippine VAT (target market; pos-domain → areas/ph-compliance.md).
// Prices are VAT-inclusive; each product has a tax class. Becomes a store setting later.

export const VAT = { rate: 0.12, inclusive: true, label: 'VAT 12%' };

export const TAX_CLASSES = {
  vatable: { label: 'VATable', color: 'blue' },
  exempt: { label: 'VAT-exempt', color: 'gold' },
  zero_rated: { label: 'Zero-rated', color: 'purple' },
};

export const TAX_CLASS_OPTIONS = Object.entries(TAX_CLASSES).map(([value, { label }]) => ({ value, label }));
