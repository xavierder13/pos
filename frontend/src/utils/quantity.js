// src/utils/quantity.js — stock quantities are decimals (3 places) so weighed items work.
// 12 → "12", 9.875 → "9.875", with the unit: "9.875 kg".

const nf = new Intl.NumberFormat(undefined, { maximumFractionDigits: 3 });

export const formatQty = (qty, unit) => {
  const text = nf.format(Number(qty) || 0);
  return unit && unit !== 'pc' ? `${text} ${unit}` : text;
};

export const UNITS = ['pc', 'kg', 'g', 'L', 'mL', 'm', 'box', 'pack'];
