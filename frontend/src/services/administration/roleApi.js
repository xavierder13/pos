import axios from '../../api/axiosInstance';

// Roles — backend RoleController (`/roles`). REST; 422 = field errors.
// Role = { id, name, read_only (the Administrator role), users_count, permissions: [{ id, name }], created_at }
// - getAll() → { data: [Role] }                              (role-list)
// - getById(id) → { data: Role + users: [{ id, name, email, active }] }     (role-list | role-edit)
// - formOptions() → { permissions: [{ id, name }] }         (role-create | role-edit)
// - create({ name, permissions: [ids] }) → 201 { data: Role }
// - update(id, same) → { data: Role }; 403 for the Administrator role
// - remove(id) → { message }; 403 Administrator, 422 still assigned to users
// - users(id) → { data: [user ids with the role], users: [{ id, name, email, active, roles: [names] }] }   (role-edit)
// - assignUsers(id, [user ids]) → { data: Role } — the role's full list: added get it, left out lose it
//     (their other roles stay); the Administrator role keeps one active user (422)
const roleApi = {
  getAll:      ()            => axios.get('/roles'),
  formOptions: ()            => axios.get('/roles/form-options'),
  getById:     (id)          => axios.get(`/roles/${id}`),
  create:      (payload)     => axios.post('/roles', payload),
  update:      (id, payload) => axios.put(`/roles/${id}`, payload),
  remove:      (id)          => axios.delete(`/roles/${id}`),
  users:       (id)          => axios.get(`/roles/${id}/users`),
  assignUsers: (id, userIds) => axios.put(`/roles/${id}/users`, { user_ids: userIds }),
};

export default roleApi;
