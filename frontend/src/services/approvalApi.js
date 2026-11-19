import axios from '../api/axiosInstance';

// Approvals — backend ApprovalController.
// Request = { id, record: { type, label, id, title, link }, flow, status (pending|approved|rejected|cancelled),
//   current_level, total_levels, requested_by, created_at, decided_at, can_decide }
// Detail = Request + { can_cancel, levels: [{ level, approver, state (approved|rejected|current|waiting), decided_by }],
//   timeline: [{ id, action, level, user, remarks, created_at }] }
// - getAll({ scope: awaiting|requested|all, status, page, per_page }) → { data, meta, awaiting_count }   (all: approval-list)
// - getById(id) → { data: Detail }
// - forRecord(type, id) → { data: Detail | null, flow: { id, name, levels } | null, can_submit }   (a view page's panel)
// - submit(type, id, remarks) → 201 { message, data } (<type>-edit) · approve(id, remarks) · reject(id, remarks) (required) · cancel(id, remarks)
const approvalApi = {
  getAll:    (params)             => axios.get('/approvals', { params }),
  getById:   (id)                 => axios.get(`/approvals/${id}`),
  forRecord: (type, id)           => axios.get(`/approvals/record/${type}/${id}`),
  submit:    (type, id, remarks)  => axios.post('/approvals/submit', { type, id, remarks }),
  approve:   (id, remarks)        => axios.post(`/approvals/${id}/approve`, { remarks }),
  reject:    (id, remarks)        => axios.post(`/approvals/${id}/reject`, { remarks }),
  cancel:    (id, remarks)        => axios.post(`/approvals/${id}/cancel`, { remarks }),
};

export default approvalApi;
