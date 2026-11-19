// src/components/approvals/RemarksModal.jsx — confirm an approval action with remarks (required for Reject).

import { useState } from 'react';
import { Form, Input, Modal } from 'antd';
import useModalForm from '../../hooks/useModalForm';

const RemarksModal = ({ action, onClose, onConfirm }) => {
  const { form, modalProps, formProps } = useModalForm();
  const [busy, setBusy] = useState(false);
  const required = action?.key === 'reject';

  const ok = async () => {
    const { remarks } = await form.validateFields();
    setBusy(true);
    try {
      await onConfirm(remarks);
    } finally {
      setBusy(false);
    }
  };

  return (
    <Modal open={!!action} title={action?.title} okText={action?.okText} keyboard={false} {...modalProps}
      okButtonProps={{ danger: action?.danger }} confirmLoading={busy} onOk={ok} onCancel={onClose}>
      <Form {...formProps} layout="vertical">
        <Form.Item name="remarks" label={required ? 'Reason' : 'Remarks (optional)'}
          rules={required ? [{ required: true, message: 'Give a reason for rejecting.' }] : []}>
          <Input.TextArea rows={4} maxLength={2000} showCount autoFocus />
        </Form.Item>
      </Form>
    </Modal>
  );
};

export default RemarksModal;
