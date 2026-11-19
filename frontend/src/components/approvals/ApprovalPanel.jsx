// src/components/approvals/ApprovalPanel.jsx
//
// A record's approval status — a view page section. Shows the latest request
// (status, levels), "Submit for approval" when the record type has an active
// flow (Administration → Approval Flows) and nothing is pending, and a link to
// the full request. Works for any record type with approvals in the backend's
// App\Support\RecordTypes:
//   <ApprovalPanel type="user" recordId={user.id} />

import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { App, Button, Empty, Space, Spin, Tag, Typography } from 'antd';
import { ArrowRightOutlined, SendOutlined } from '@ant-design/icons';
import approvalApi from '../../services/approvalApi';
import handleApiError from '../../utils/handleApiError';
import { formatDateTime } from '../../utils/formatDate';
import ViewSection from '../ViewSection';
import ApprovalLevels from './ApprovalLevels';
import RemarksModal from './RemarksModal';
import { STATUS } from './approvalStatus';

const ApprovalPanel = ({ type, recordId, title = 'Approval' }) => {
  const { message } = App.useApp();
  const [state, setState] = useState(null);   // { data, flow, can_submit }
  const [submitting, setSubmitting] = useState(false);

  const load = useCallback(async () => {
    try {
      const { data } = await approvalApi.forRecord(type, recordId);
      setState(data);
    } catch (error) {
      if (error.response?.status !== 403) handleApiError(error, message);
      setState({ data: null, flow: null, can_submit: false, hidden: true });
    }
  }, [type, recordId, message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const submit = async (remarks) => {
    try {
      const { data } = await approvalApi.submit(type, recordId, remarks);
      message.success(data.message);
      setSubmitting(false);
      load();
    } catch (error) {
      handleApiError(error, message);
    }
  };

  if (state?.hidden) return null;
  const request = state?.data;

  return (
    <ViewSection title={title} extra={state?.can_submit && (
      <Button color="cyan" variant="outlined" size="small" icon={<SendOutlined />} onClick={() => setSubmitting(true)}>
        {request ? 'Submit again' : 'Submit for approval'}
      </Button>
    )}>
      {!state ? <Spin /> : !request ? (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} style={{ margin: '8px 0' }}
          description={state.flow ? `Not submitted yet · flow “${state.flow.name}” (${state.flow.levels} level${state.flow.levels === 1 ? '' : 's'})` : 'No approval flow is set up for this kind of record.'} />
      ) : (
        <Space orientation="vertical" size={12} style={{ width: '100%' }}>
          <Space wrap>
            <Tag color={STATUS[request.status]?.color}>{STATUS[request.status]?.label}</Tag>
            <Typography.Text type="secondary">Requested by {request.requested_by} · {formatDateTime(request.created_at)}</Typography.Text>
            <Link to={`/approvals/${request.id}`}>Open request <ArrowRightOutlined /></Link>
          </Space>
          <ApprovalLevels levels={request.levels} />
        </Space>
      )}
      <RemarksModal action={submitting ? { key: 'submit', title: 'Submit for approval', okText: 'Submit' } : null}
        onClose={() => setSubmitting(false)} onConfirm={submit} />
    </ViewSection>
  );
};

export default ApprovalPanel;
