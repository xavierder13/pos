// src/pages/errors/UnauthorizePage.jsx

import { Button, Result } from 'antd';
import { useNavigate } from 'react-router-dom';

const UnauthorizePage = () => {
  const navigate = useNavigate();
  return <Result status="403" title="No access" subTitle="You don't have permission to open that page. Ask an administrator."
    extra={<Button type="primary" onClick={() => navigate('/')}>Back to the dashboard</Button>} />;
};

export default UnauthorizePage;
