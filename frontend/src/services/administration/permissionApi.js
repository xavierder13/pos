import axios from '../../api/axiosInstance';

// Permissions — backend PermissionController (`/permissions`). Created by the
// backend's seeders; which roles have each one can be changed here.
// Permission = { id, name, module, roles: [{ id, name, users_count }] }
// - getAll() → { data: [Permission] } (by name)                          (permission-list)
// - roleOptions() → { roles: [{ id, name, users_count, read_only }] }     (role-edit)
// - assignRoles(id, [role ids]) → { data: Permission } — the permission's full list of
//     roles: added get it, left out lose it; the Administrator role always keeps it (role-edit)
const permissionApi = {
  getAll:      ()            => axios.get('/permissions'),
  roleOptions: ()            => axios.get('/permissions/role-options'),
  assignRoles: (id, roleIds) => axios.put(`/permissions/${id}/roles`, { role_ids: roleIds }),
};

export default permissionApi;
