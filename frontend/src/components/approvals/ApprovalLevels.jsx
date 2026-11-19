// src/components/approvals/ApprovalLevels.jsx — a request's levels as steps (who approves, who decided).

import { Steps } from 'antd';
import { COLORS } from '../../utils/theme';
import { CheckCircleFilled, ClockCircleOutlined, CloseCircleFilled, LoadingOutlined } from '@ant-design/icons';

const ICON = {
  approved: <CheckCircleFilled style={{ color: COLORS.success }} />,
  rejected: <CloseCircleFilled style={{ color: COLORS.error }} />,
  current: <LoadingOutlined style={{ color: 'var(--accent)' }} />,
  waiting: <ClockCircleOutlined style={{ color: 'var(--muted)' }} />,
};

const ApprovalLevels = ({ levels, orientation = 'horizontal' }) => (
  <Steps size="small" orientation={orientation}
    items={levels.map((l) => ({
      title: `Level ${l.level}`,
      content: l.decided_by ? `${l.approver} · ${l.state} by ${l.decided_by}` : `${l.approver}${l.state === 'current' ? ' · waiting for decision' : ''}`,
      icon: ICON[l.state],
      status: l.state === 'approved' ? 'finish' : l.state === 'rejected' ? 'error' : l.state === 'current' ? 'process' : 'wait',
    }))} />
);

export default ApprovalLevels;
