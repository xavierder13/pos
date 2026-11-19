// src/components/StatusClock.jsx
//
// The shell's status cluster: a live clock (cashiers and managers watch the time
// for shifts and cut-offs) and the connection pill (an offline register must be
// obvious at a glance). `compact` drops the date.

import { useEffect, useState } from 'react';
import { Tooltip } from 'antd';
import dayjs from 'dayjs';
import useOnlineStatus from '../hooks/useOnlineStatus';

const StatusClock = ({ compact = false }) => {
  const online = useOnlineStatus();
  const [now, setNow] = useState(() => dayjs());

  useEffect(() => {
    const id = setInterval(() => setNow(dayjs()), 15_000);
    return () => clearInterval(id);
  }, []);

  return (
    <div className="status-cluster">
      <Tooltip title={online ? 'Connected' : 'No network connection: changes cannot be saved'}>
        <span className={`net-pill ${online ? 'is-online' : 'is-offline'}`} role="status">
          <span className="net-pill__dot" />
          {online ? 'Online' : 'Offline'}
        </span>
      </Tooltip>
      <span className="status-clock">
        <strong>{now.format('h:mm A')}</strong>
        {!compact && <span>{now.format('ddd, MMM D')}</span>}
      </span>
    </div>
  );
};

export default StatusClock;
