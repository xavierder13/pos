// src/pages/errors/NotFoundPage.jsx

import { Button, Result } from 'antd';
import { useNavigate } from 'react-router-dom';

const NotFoundPage = () => {
  const navigate = useNavigate();
  return <Result status="404" title="Page not found" subTitle="The page you opened doesn't exist."
    extra={<Button type="primary" onClick={() => navigate('/')}>Back to the dashboard</Button>} />;
};

export default NotFoundPage;
