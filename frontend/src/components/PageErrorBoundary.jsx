// src/components/PageErrorBoundary.jsx
//
// A page that throws while rendering shows what failed, with Reload and
// Back, instead of a blank page — the layout stays. `resetKey` (the path)
// clears it on navigation.

import { Component } from 'react';
import { Alert, Button, Space, Typography } from 'antd';

const { Text } = Typography;

class PageErrorBoundary extends Component {
  constructor(props) {
    super(props);
    this.state = { error: null, key: props.resetKey };
  }

  static getDerivedStateFromError(error) {
    return { error };
  }

  static getDerivedStateFromProps(props, state) {
    return props.resetKey !== state.key ? { error: null, key: props.resetKey } : null;
  }

  componentDidCatch(error, info) {
    console.error('Page failed to render:', error, info?.componentStack);
  }

  render() {
    const { error } = this.state;
    if (!error) return this.props.children;
    return (
      <Alert type="error" showIcon
        title="This page ran into a problem and couldn't be shown."
        description={(
          <Space orientation="vertical" size={8}>
            <Text code copyable>{String(error?.message || error)}</Text>
            <Space>
              <Button type="primary" onClick={() => window.location.reload()}>Reload</Button>
              <Button onClick={() => window.history.back()}>Back</Button>
            </Space>
          </Space>
        )} />
    );
  }
}

export default PageErrorBoundary;
