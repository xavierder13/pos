import axios from '../../api/axiosInstance';

// Inventory — backend InventoryController (`/inventory`). Quantities are decimals (3 places, signed for
// adjustments); money (cost) is integer centavos.
// StockRow = { id, sku, barcode, name, unit, category: {id,name}|null, quantity, reorder_level, cost, active }
// Movement = { id, product: {id,sku,name,unit}, location: {id,code,name}, quantity, balance_after, unit_cost,
//              document: { id, number, type, reason, reference, supplier, user: {id,name} }, created_at }
// Document = { id, number, type: receipt|adjustment|transfer|opening, location: {id,code,name}, to_location|null,
//              reason, supplier, reference, note, user: {id,name}, lines_count?, movements?: [Movement], created_at }
// - stock({ location_id, search, category_id, status: low|out|negative, page, per_page, sort: sku|name|quantity, order })
//     → { data: [StockRow], meta: { current_page, per_page, total } }                       (inventory-list)
// - lowStock({ location_id }) → { data: [{ id, sku, name, unit, location: {id,code,name}, quantity, reorder_level }] }
//     (inventory-list; ≤ 8 product × location pairs at or below reorder, emptiest first)
// - levels(productId) → { data: [{ location: {id,code,name,type,active}, quantity }] }      (inventory-list | product-list)
// - movements({ product_id, location_id, type, date_from, date_to, page, per_page }) → { data: [Movement], meta } (inventory-list)
// - documents({ type, location_id, search, page, per_page }) → { data: [Document], meta }   (inventory-list)
// - document(id) → { data: Document with movements }                                          (inventory-list)
// - receive({ location_id, supplier, reference, note, lines: [{ product_id, quantity > 0, unit_cost? }] }) → 201 { data: Document, message } (inventory-receive)
// - adjust({ location_id, reason, note, lines: [{ product_id, quantity ≠ 0 }] }) → 201 …     (inventory-adjust; 422 if below zero)
// - transfer({ location_id, to_location_id, note, lines: [{ product_id, quantity > 0 }] }) → 201 … (inventory-transfer; 422 if below zero)
const inventoryApi = {
  stock:     (params)  => axios.get('/inventory/stock', { params }),
  lowStock:  (params)  => axios.get('/inventory/low-stock', { params }),
  levels:    (id)      => axios.get(`/inventory/products/${id}/levels`),
  movements: (params)  => axios.get('/inventory/movements', { params }),
  documents: (params)  => axios.get('/inventory/documents', { params }),
  document:  (id)      => axios.get(`/inventory/documents/${id}`),
  receive:   (payload) => axios.post('/inventory/receipts', payload),
  adjust:    (payload) => axios.post('/inventory/adjustments', payload),
  transfer:  (payload) => axios.post('/inventory/transfers', payload),
};

export const DOC_TYPES = {
  receipt: { label: 'Receipt', color: 'green' },
  adjustment: { label: 'Adjustment', color: 'orange' },
  transfer: { label: 'Transfer', color: 'blue' },
  opening: { label: 'Opening', color: 'default' },
};

export const ADJUST_REASONS = [
  { value: 'damaged', label: 'Damaged' },
  { value: 'expired', label: 'Expired' },
  { value: 'lost', label: 'Lost / shrinkage' },
  { value: 'found', label: 'Found' },
  { value: 'count', label: 'Stock count' },
  { value: 'correction', label: 'Correction' },
  { value: 'internal_use', label: 'Internal use' },
];

export default inventoryApi;
