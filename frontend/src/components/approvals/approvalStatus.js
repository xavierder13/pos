// src/components/approvals/approvalStatus.js — status → tag colour / label, shared by the approval pages.

export const STATUS = {
  pending: { color: 'gold', label: 'Pending' },
  approved: { color: 'green', label: 'Approved' },
  rejected: { color: 'red', label: 'Rejected' },
  cancelled: { color: 'default', label: 'Cancelled' },
};

export const ACTION_LABELS = { submitted: 'Submitted', approved: 'Approved', rejected: 'Rejected', cancelled: 'Cancelled' };
