// src/pages/dashboard/DashboardPage.jsx
//
// The store's day at a glance: today's sales KPIs, quick actions (the register
// first), sales by hour, recent sales and low stock (live, all locations). The sales
// KPIs and lists are empty placeholders until the Sales module feeds them: they never
// show made-up numbers.

import { useEffect, useState } from 'react';
import { Card, Col, Empty, Row, Spin, Tag } from 'antd';
import {
  CheckSquareOutlined, DollarOutlined, FileTextOutlined, InboxOutlined, SafetyOutlined, ShopOutlined,
  ImportOutlined, ShoppingOutlined, TagsOutlined, TeamOutlined, TransactionOutlined,
} from '@ant-design/icons';
import { Link, useNavigate } from 'react-router-dom';
import inventoryApi from '../../services/inventory/inventoryApi';
import { formatQty } from '../../utils/quantity';
import dayjs from 'dayjs';
import PageHeader from '../../components/PageHeader';
import useAuth from '../../hooks/useAuth';
import { REGISTER_PATH, REGISTER_PERMISSION } from '../../config/menu';
import { COLORS } from '../../utils/theme';
import { formatMoney } from '../../utils/money';

const greeting = () => {
  const h = dayjs().hour();
  if (h < 12) return 'Good morning';
  if (h < 18) return 'Good afternoon';
  return 'Good evening';
};

const PENDING = 'Live once the Sales module is built';

const KPIS = [
  { key: 'sales', label: "Today's sales", value: formatMoney(0), icon: <DollarOutlined />, tone: COLORS.accent },
  { key: 'tx', label: 'Transactions', value: '0', icon: <TransactionOutlined />, tone: COLORS.info },
  { key: 'avg', label: 'Average sale', value: '—', icon: <FileTextOutlined />, tone: COLORS.success },
  { key: 'items', label: 'Items sold', value: '0', icon: <ShoppingOutlined />, tone: COLORS.actionDownload },
];

const Kpi = ({ label, value, icon, tone }) => (
  <Card className="kpi-card">
    <div className="kpi" style={{ '--kpi': tone }}>
      <span className="kpi__icon">{icon}</span>
      <div>
        <div className="kpi__label">{label}</div>
        <div className="kpi__value">{value}</div>
        <div className="kpi__hint">{PENDING}</div>
      </div>
    </div>
  </Card>
);

const DashboardPage = () => {
  const navigate = useNavigate();
  const { user, can } = useAuth();
  const firstName = user?.name?.split(/\s+/)[0] ?? '';
  const canSeeStock = can('inventory-list');
  const [low, setLow] = useState({ items: [], loading: canSeeStock });

  useEffect(() => {
    if (!canSeeStock) return undefined;
    let alive = true;
    inventoryApi.lowStock()
      .then(({ data }) => alive && setLow({ items: data.data, loading: false }))
      .catch(() => alive && setLow({ items: [], loading: false }));
    return () => { alive = false; };
  }, [canSeeStock]);

  const actions = [
    can(REGISTER_PERMISSION) && { key: 'register', primary: true, icon: <ShopOutlined />, label: 'Open Register', hint: 'Start selling', path: REGISTER_PATH },
    can('inventory-receive') && { key: 'receive', icon: <ImportOutlined />, label: 'Stock', hint: 'Receive, adjust, transfer', path: '/inventory/stock' },
    can('product-list') && { key: 'products', icon: <TagsOutlined />, label: 'Products', hint: 'Prices, barcodes, VAT', path: '/products' },
    { key: 'approvals', icon: <CheckSquareOutlined />, label: 'Approvals', hint: 'Requests waiting on you', path: '/approvals' },
    can('user-list') && { key: 'users', icon: <TeamOutlined />, label: 'Staff accounts', hint: 'Cashiers, managers', path: '/administration/users' },
    can('role-list') && { key: 'roles', icon: <SafetyOutlined />, label: 'Roles', hint: 'Who can do what', path: '/administration/roles' },
  ].filter(Boolean);

  return (
    <>
      <PageHeader title={`${greeting()}, ${firstName}`} subtitle={dayjs().format('dddd, MMMM D, YYYY')} />

      <Row gutter={[16, 16]}>
        {KPIS.map(({ key, ...k }) => (
          <Col key={key} xs={24} sm={12} xl={6}><Kpi {...k} /></Col>
        ))}

        <Col xs={24} xl={10}>
          <Card title="Quick actions" style={{ height: '100%' }}>
            <div className="quick-actions">
              {actions.map((a) => (
                <button key={a.key} type="button" className={`quick-action${a.primary ? ' quick-action--primary' : ''}`} onClick={() => navigate(a.path)}>
                  {a.icon}
                  <span>{a.label}<br /><small>{a.hint}</small></span>
                </button>
              ))}
            </div>
          </Card>
        </Col>

        <Col xs={24} xl={14}>
          <Card title="Sales by hour" extra={<span className="kpi__hint">Today</span>} style={{ height: '100%' }}>
            <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No sales recorded today" />
          </Card>
        </Col>

        <Col xs={24} lg={14}>
          <Card title="Recent sales">
            <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Completed sales will be listed here" />
          </Card>
        </Col>

        <Col xs={24} lg={10}>
          <Card title={<span><InboxOutlined /> Low stock</span>}
            extra={canSeeStock && <Link to="/inventory/stock">View stock</Link>}>
            {canSeeStock ? (
              low.loading ? <Spin style={{ display: 'block', margin: '24px auto' }} />
                : low.items.length === 0 ? <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Nothing at or below its reorder level" />
                  : (
                    <ul className="low-list">
                      {low.items.map((p) => (
                        <li key={`${p.id}-${p.location.id}`}>
                          <div>
                            <Link to={`/products/${p.id}`}>{p.name}</Link>
                            <small><Tag>{p.location.code}</Tag>{p.sku} · reorder at {formatQty(p.reorder_level)}</small>
                          </div>
                          {p.quantity <= 0 && <Tag color="error">Out</Tag>}
                          <span className="money" style={{ color: p.quantity <= 0 ? COLORS.error : COLORS.warning }}>{formatQty(p.quantity, p.unit)}</span>
                        </li>
                      ))}
                    </ul>
                  )
            ) : (
              <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="You don't have access to stock" />
            )}
          </Card>
        </Col>
      </Row>
    </>
  );
};

export default DashboardPage;
