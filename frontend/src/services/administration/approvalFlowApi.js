import axios from '../../api/axiosInstance';

// Approval Flows — backend ApprovalFlowController (approval-flow-edit). Stored in the database: no code change.
// Flow = { id, name, record_type, record_label, active, pending_count,
//   levels: [{ level, approver_type (user|role), approver_id, approver }] }
// - getAll() → { data: [Flow] } · options() → { record_types: [{ value, label }], users: [{ id, name, email }], roles: [{ id, name }] }
// - create({ name, record_type, active, levels: [{ approver_type, approver_id }] }) → 201 { message, data }
// - update(id, same) → { message, data } — activating one deactivates the type's other flows
// - remove(id) → { message }; 422 when it has requests (deactivate instead)
const approvalFlowApi = {
  getAll:  ()            => axios.get('/approval-flows'),
  options: ()            => axios.get('/approval-flows/options'),
  create:  (payload)     => axios.post('/approval-flows', payload),
  update:  (id, payload) => axios.put(`/approval-flows/${id}`, payload),
  remove:  (id)          => axios.delete(`/approval-flows/${id}`),
};

export default approvalFlowApi;
