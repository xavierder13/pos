import axios from '../../api/axiosInstance';

// Users — backend UserController (`/users`). REST; 422 = field errors.
// User = { id, name, email, active, must_change_password, last_login_at, roles: [{ id, name }], created_at }
// - getById(id) → { data: User } (roles with their permissions)        (user-list | user-edit)
// - getAll({ page, per_page, search, active, role_id, sort, order })
//     → { data: [User], meta: { current_page, per_page, total } }   (user-list)
// - formOptions() → { roles: [{ id, name }] }                      (user-create | user-edit)
// - create({ name, email, password, password_confirmation, active, must_change_password, roles: [ids] }) → 201 { data: User }
// - update(id, same — password only to change it) → { data: User }
// - remove(id) → { message }; 422 { message } for your own account or the last Administrator
// - export({ search, active, role_id, sort, order }) → users-<date>.csv (blob; the whole filtered list)   (user-list)
const userApi = {
  getAll:      (params)      => axios.get('/users', { params }),
  formOptions: ()            => axios.get('/users/form-options'),
  getById:     (id)          => axios.get(`/users/${id}`),
  create:      (payload)     => axios.post('/users', payload),
  update:      (id, payload) => axios.put(`/users/${id}`, payload),
  remove:      (id)          => axios.delete(`/users/${id}`),
  export:      (params)      => axios.get('/users/export', { params, responseType: 'blob' }),
};

export default userApi;
