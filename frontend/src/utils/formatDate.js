// src/utils/formatDate.js

import dayjs from 'dayjs';

export const DATE_FORMAT = 'MM/DD/YYYY';
export const DATE_TIME_FORMAT = 'MM/DD/YYYY hh:mm A';

export const formatDate = (value) => (value ? dayjs(value).format(DATE_FORMAT) : '—');
export const formatDateTime = (value) => (value ? dayjs(value).format(DATE_TIME_FORMAT) : '—');
