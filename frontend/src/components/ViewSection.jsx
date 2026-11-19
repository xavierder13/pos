// src/components/ViewSection.jsx
//
// One titled section of a record's view page: a card with label / value rows.
//   <ViewSection title="Account" items={[['Email', user.email], ['Status', <Tag>…</Tag>]]} />
// Empty values show as "—". `extra` goes on the card's title row; `children`
// replaces the rows (a table, a panel …).

import { Card, Descriptions } from 'antd';

const show = (value) => (value === null || value === undefined || value === '' ? '—' : value);

const ViewSection = ({ title, items = [], extra, column = 1, children }) => (
  <Card title={title} extra={extra} size="small" styles={{ body: { padding: children ? 16 : 0 } }}>
    {children ?? (
      <Descriptions size="small" bordered column={column} styles={{ label: { width: 180 } }}
        items={items.map(([label, value]) => ({ key: label, label, children: show(value) }))} />
    )}
  </Card>
);

export default ViewSection;
