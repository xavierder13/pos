// src/utils/initials.js — "Maria dela Cruz" → "MD" (avatars)

export const initials = (name = '') => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('') || '?';
