// src/pages/approvals/ApprovalView.jsx
//
// /approvals/:id — one request: the record, its levels, the timeline, and
// Approve / Reject (the current level's approver or the Administrator) or
// Cancel (the requester). Reject needs a reason.

import { useCallback, useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { App, Button, Col, Row, Spin, Tag } from 'antd';
import { CheckOutlined, CloseOutlined, StopOutlined } from '@ant-design/icons';
import ViewHeader from '../../components/ViewHeader';
import ViewSection from '../../components/ViewSection';
import RecordNotFound from '../../components/RecordNotFound';
import approvalApi from '../../services/approvalApi';
import handleApiError from '../../utils/handleApiError';
import { formatDateTime } from '../../utils/formatDate';
import ApprovalLevels from '../../components/approvals/ApprovalLevels';
import ApprovalTimeline from '../../components/approvals/ApprovalTimeline';
import RemarksModal from '../../components/approvals/RemarksModal';
import { STATUS } from '../../components/approvals/approvalStatus';

const ACTIONS = {
  approve: { key: 'approve', title: 'Approve this request', okText: 'Approve', call: approvalApi.approve },
  reject: { key: 'reject', title: 'Reject this request', okText: 'Reject', danger: true, call: approvalApi.reject },
  cancel: { key: 'cancel', title: 'Cancel this request', okText: 'Cancel request', danger: true, call: approvalApi.cancel },
};

const ApprovalView = () => {
  const { id } = useParams();
  const { message } = App.useApp();
  const [request, setRequest] = useState(null);
  const [state, setState] = useState('loading');
  const [action, setAction] = useState(null);

  const load = useCallback(async () => {
    try {
      const { data } = await approvalApi.getById(id);
      setRequest(data.data);
      setState('ready');
    } catch (error) {
      if (error.response?.status !== 404) handleApiError(error, message);
      setState((s) => (s === 'loading' ? 'missing' : s));
    }
  }, [id, message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const confirm = async (remarks) => {
    try {
      const { data } = await action.call(request.id, remarks);
      message.success(data.message);
      setRequest(data.data);
      setAction(null);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  if (state === 'loading') return <Spin size="large" style={{ display: 'block', margin: '80px auto' }} />;
  if (state === 'missing') return <RecordNotFound title="Request not found" backTo="/approvals" />;

  const record = request.record;

  return (
    <>
      <ViewHeader
        backTo="/approvals"
        title={record ? `${record.label}: ${record.title}` : 'Deleted record'}
        tags={<Tag color={STATUS[request.status]?.color}>{STATUS[request.status]?.label}</Tag>}
        subtitle={`Request #${request.id} · ${request.flow}`}
        extra={<>
          {request.can_decide && <Button type="primary" icon={<CheckOutlined />} onClick={() => setAction(ACTIONS.approve)}>Approve</Button>}
          {request.can_decide && <Button danger icon={<CloseOutlined />} onClick={() => setAction(ACTIONS.reject)}>Reject</Button>}
          {request.can_cancel && <Button color="orange" variant="outlined" icon={<StopOutlined />} onClick={() => setAction(ACTIONS.cancel)}>Cancel request</Button>}
        </>}
      />
      <Row gutter={[16, 16]}>
        <Col span={24}>
          <ViewSection title="Approval levels"><ApprovalLevels levels={request.levels} /></ViewSection>
        </Col>
        <Col xs={24} xl={10}>
          <ViewSection title="Request" items={[
            ['Record', record ? <Link key="r" to={record.link}>{record.label}: {record.title}</Link> : null],
            ['Flow', request.flow],
            ['Status', <Tag key="s" color={STATUS[request.status]?.color}>{STATUS[request.status]?.label}</Tag>],
            ['Level', `${Math.min(request.current_level, request.total_levels)} of ${request.total_levels}`],
            ['Requested by', request.requested_by],
            ['Submitted', formatDateTime(request.created_at)],
            ['Decided', request.decided_at && formatDateTime(request.decided_at)],
          ]} />
        </Col>
        <Col xs={24} xl={14}>
          <ViewSection title="Timeline"><ApprovalTimeline timeline={request.timeline} /></ViewSection>
        </Col>
      </Row>
      <RemarksModal action={action} onClose={() => setAction(null)} onConfirm={confirm} />
    </>
  );
};

export default ApprovalView;
