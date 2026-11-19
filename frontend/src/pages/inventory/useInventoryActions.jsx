// src/pages/inventory/useInventoryActions.jsx — the Receive / Adjust / Transfer buttons (permission-gated)
// and the posting dialog, shared by the Stock and Documents pages.

import { useState } from 'react';
import { Button } from 'antd';
import { ImportOutlined, SlidersOutlined, SwapOutlined } from '@ant-design/icons';
import useAuth from '../../hooks/useAuth';
import StockDocumentModal from './components/StockDocumentModal';

const useInventoryActions = ({ locationId, onPosted }) => {
  const { can } = useAuth();
  const [kind, setKind] = useState(null);

  const buttons = [
    can('inventory-receive') && <Button key="r" type="primary" icon={<ImportOutlined />} onClick={() => setKind('receive')}>Receive</Button>,
    can('inventory-adjust') && <Button key="a" icon={<SlidersOutlined />} onClick={() => setKind('adjust')}>Adjust</Button>,
    can('inventory-transfer') && <Button key="t" icon={<SwapOutlined />} onClick={() => setKind('transfer')}>Transfer</Button>,
  ].filter(Boolean);

  const modal = kind && (
    <StockDocumentModal kind={kind} defaults={{ location_id: locationId ?? undefined }} onClose={() => setKind(null)}
      onPosted={(doc) => { setKind(null); onPosted?.(doc); }} />
  );

  return { buttons, modal };
};

export default useInventoryActions;
