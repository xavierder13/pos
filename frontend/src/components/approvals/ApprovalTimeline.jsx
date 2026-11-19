// src/components/approvals/ApprovalTimeline.jsx — who did what on a request, with remarks.

import { Timeline, Typography } from 'antd';
import { formatDateTime } from '../../utils/formatDate';
import { ACTION_LABELS } from './approvalStatus';

const COLORS = { submitted: 'blue', approved: 'green', rejected: 'red', cancelled: 'gray' };

const ApprovalTimeline = ({ timeline }) => (
  <Timeline items={timeline.map((t) => ({
    key: t.id,
    color: COLORS[t.action],
    content: (
      <div>
        <Typography.Text strong>{ACTION_LABELS[t.action] ?? t.action}</Typography.Text>
        {t.level ? <Typography.Text type="secondary"> · level {t.level}</Typography.Text> : null}
        <Typography.Text type="secondary"> · {t.user ?? 'someone'} · {formatDateTime(t.created_at)}</Typography.Text>
        {t.remarks && <Typography.Paragraph style={{ margin: '4px 0 0', whiteSpace: 'pre-wrap' }}>“{t.remarks}”</Typography.Paragraph>}
      </div>
    ),
  }))} />
);

export default ApprovalTimeline;
