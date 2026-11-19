import axios from '../../api/axiosInstance';

// Audit Trail — backend AuditTrailController (`/audit-trail`, audit-trail-list).
// - getAll({ page, per_page, module, action, causer_id, record, record_id (one record's history; needs record), search, date_from, date_to, sort, order })
//     → { data: [{ id, created_at, module, action, description, record, record_id, user,
//                  changes: [{ field, old, new }] }], meta: { current_page, per_page, total } }
// - options() → { data: { modules: [..], records: [..], users: [{ id, name }] } }
const auditTrailApi = {
  getAll:  (params) => axios.get('/audit-trail', { params }),
  options: ()       => axios.get('/audit-trail/options'),
};

export default auditTrailApi;
