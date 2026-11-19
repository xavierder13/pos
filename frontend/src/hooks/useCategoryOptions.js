// src/hooks/useCategoryOptions.js — active categories as Select options ({ value: id, label: name }).

import { useEffect, useState } from 'react';
import categoryApi from '../services/categories/categoryApi';

const useCategoryOptions = () => {
  const [options, setOptions] = useState([]);

  useEffect(() => {
    let alive = true;
    categoryApi.getAll({ active: 1, per_page: 100, sort: 'name', order: 'asc' })
      .then(({ data }) => alive && setOptions(data.data.map((c) => ({ value: c.id, label: c.name }))))
      .catch(() => {});
    return () => { alive = false; };
  }, []);

  return options;
};

export default useCategoryOptions;
