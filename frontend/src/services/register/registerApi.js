import axios from '../../api/axiosInstance';

// Register — backend RegisterCatalogController.
// - catalog() → { categories: [{ id, name }],
//                 products: [{ id, sku, barcode, name, category_id, unit, price (centavos), tax_class: vatable|exempt|zero_rated,
//                              sc_pwd_eligible, track_stock }],
//                 locations: [{ id, code, name }] }   (active store locations)   (register-use)
const registerApi = {
  catalog: () => axios.get('/register/catalog'),
};

export default registerApi;
