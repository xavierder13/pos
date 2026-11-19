// src/pages/register/HeldSalesDrawer.jsx — parked sales: resume one (the sale on
// screen, if any, is parked in its place) or discard it.

import { Button, Drawer, Empty, Popconfirm } from 'antd';
import dayjs from 'dayjs';
import { formatMoney } from '../../utils/money';

const HeldSalesDrawer = ({ open, held, totalOf, onResume, onDrop, onClose }) => (
  <Drawer open={open} onClose={onClose} keyboard={false} title={`Held sales (${held.length})`} size={380}>
    {held.length === 0 ? (
      <Empty description="No held sales" />
    ) : (
      <ul className="held-list">
        {held.map((h) => (
          <li key={h.key}>
            <div className="held-list__main">
              <strong>{h.label || `${h.lines.length} item${h.lines.length === 1 ? '' : 's'}`}</strong>
              <span>Held {dayjs(h.heldAt).format('h:mm A')} · {h.lines.map((l) => l.name).slice(0, 2).join(', ')}{h.lines.length > 2 ? '…' : ''}</span>
            </div>
            <span className="money held-list__total">{formatMoney(totalOf(h))}</span>
            <div className="held-list__actions">
              <Button type="primary" onClick={() => onResume(h.key)}>Resume</Button>
              <Popconfirm title="Discard this held sale?" okText="Discard" okButtonProps={{ danger: true }} onConfirm={() => onDrop(h.key)}>
                <Button danger>Discard</Button>
              </Popconfirm>
            </div>
          </li>
        ))}
      </ul>
    )}
  </Drawer>
);

export default HeldSalesDrawer;
