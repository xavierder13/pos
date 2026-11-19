// src/components/RecordNotFound.jsx — a view page whose record doesn't exist (or can't be read).

import { Button, Result } from 'antd';
import { useNavigate } from 'react-router-dom';

const RecordNotFound = ({ title = 'Record not found', subTitle, backTo, backLabel = 'Back to list' }) => {
  const navigate = useNavigate();
  return (
    <Result status="404" title={title}
      subTitle={subTitle ?? 'It may have been deleted, or the link is wrong.'}
      extra={backTo && <Button type="primary" onClick={() => navigate(backTo)}>{backLabel}</Button>} />
  );
};

export default RecordNotFound;
