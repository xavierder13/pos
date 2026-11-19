// src/components/Attachments.jsx
//
// Files on a record — a view page section. Drag & drop or click to upload
// (with progress), download, delete. Works for any record registered in the
// backend's App\Support\RecordTypes:
//   <Attachments type="user" recordId={user.id} />
// Seeing them needs `<type>-list`; adding / removing needs `<type>-edit`.

import { useCallback, useEffect, useState } from 'react';
import { App, Popconfirm, Progress, Space, Table, Typography, Upload } from 'antd';
import ActionButton from './ActionButton';
import { COLORS } from '../utils/theme';
import { FileExcelOutlined, FileImageOutlined, FileOutlined, FilePdfOutlined, FileTextOutlined, FileWordOutlined, FileZipOutlined, InboxOutlined } from '@ant-design/icons';
import attachmentApi from '../services/attachmentApi';
import useAuth from '../hooks/useAuth';
import handleApiError from '../utils/handleApiError';
import downloadFile from '../utils/downloadFile';
import formatBytes from '../utils/formatBytes';
import { formatDateTime } from '../utils/formatDate';
import ViewSection from './ViewSection';

const ICONS = [
  [/pdf/, <FilePdfOutlined key="p" style={{ color: COLORS.error }} />],
  [/word|document/, <FileWordOutlined key="w" style={{ color: '#4f8cff' }} />],
  [/sheet|excel|csv/, <FileExcelOutlined key="x" style={{ color: COLORS.success }} />],
  [/image/, <FileImageOutlined key="i" style={{ color: 'var(--accent)' }} />],
  [/zip|compressed/, <FileZipOutlined key="z" style={{ color: COLORS.warning }} />],
  [/text/, <FileTextOutlined key="t" />],
];
const iconFor = (mime = '') => ICONS.find(([re]) => re.test(mime))?.[1] ?? <FileOutlined />;

const Attachments = ({ type, recordId, title = 'Attachments' }) => {
  const { message } = App.useApp();
  const { can } = useAuth();
  const canManage = can(`${type}-edit`);
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [uploads, setUploads] = useState({});   // uid → { name, percent }

  const load = useCallback(async () => {
    try {
      const { data } = await attachmentApi.list(type, recordId);
      setRows(data.data);
    } catch (error) {
      handleApiError(error, message);
    } finally {
      setLoading(false);
    }
  }, [type, recordId, message]);

  useEffect(() => {
    const run = async () => { await load(); };
    run();
  }, [load]);

  const upload = async ({ file, onSuccess, onError }) => {
    setUploads((u) => ({ ...u, [file.uid]: { name: file.name, percent: 0 } }));
    try {
      const { data } = await attachmentApi.upload(type, recordId, file, (percent) => setUploads((u) => ({ ...u, [file.uid]: { name: file.name, percent } })));
      setRows((r) => [data.data, ...r]);
      message.success(`${file.name} uploaded.`);
      onSuccess(data);
    } catch (error) {
      handleApiError(error, message);
      onError(error);
    } finally {
      setUploads((u) => { const next = { ...u }; delete next[file.uid]; return next; });
    }
  };

  const download = async (row) => {
    try {
      await downloadFile(await attachmentApi.download(row.id), row.name, message);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  const remove = async (row) => {
    try {
      const { data } = await attachmentApi.remove(row.id);
      setRows((r) => r.filter((x) => x.id !== row.id));
      message.success(data.message);
    } catch (error) {
      handleApiError(error, message);
    }
  };

  return (
    <ViewSection title={title} extra={<Typography.Text type="secondary">{rows.length} file{rows.length === 1 ? '' : 's'}</Typography.Text>}>
      {canManage && (
        <Upload.Dragger multiple showUploadList={false} customRequest={upload} style={{ marginBottom: 12 }}>
          <p className="ant-upload-drag-icon"><InboxOutlined style={{ color: 'var(--accent)' }} /></p>
          <p className="ant-upload-text">Drop files here, or click to choose</p>
          <p className="ant-upload-hint">PDF, Office, images, text or zip, up to 10 MB each</p>
        </Upload.Dragger>
      )}
      {Object.entries(uploads).map(([uid, u]) => (
        <div key={uid} style={{ marginBottom: 8 }}><Typography.Text type="secondary">{u.name}</Typography.Text><Progress percent={u.percent} size="small" /></div>
      ))}
      <Table rowKey="id" size="small" loading={loading} dataSource={rows} pagination={{ pageSize: 10, hideOnSinglePage: true, showSizeChanger: false }}
        locale={{ emptyText: 'No files yet' }}
        columns={[
          {
            title: 'File', dataIndex: 'name',
            render: (name, r) => (
              <Space align="start">
                <span style={{ fontSize: 18 }}>{iconFor(r.mime_type)}</span>
                <div style={{ minWidth: 0 }}>
                  <Typography.Link onClick={() => download(r)} style={{ wordBreak: 'break-all' }}>{name}</Typography.Link>
                  <div><Typography.Text type="secondary" style={{ fontSize: 12 }}>{formatBytes(r.size)} · {r.uploaded_by || '—'} · {formatDateTime(r.created_at)}</Typography.Text></div>
                </div>
              </Space>
            ),
          },
          {
            title: '', key: 'actions', width: 80,
            render: (_, r) => (
              <Space size={6}>
                <ActionButton kind="download" onClick={() => download(r)} />
                {canManage && (
                  <Popconfirm title="Delete this file?" description={r.name} okText="Delete" okButtonProps={{ danger: true }} onConfirm={() => remove(r)}>
                    <ActionButton kind="delete" />
                  </Popconfirm>
                )}
              </Space>
            ),
          },
        ]} />
    </ViewSection>
  );
};

export default Attachments;
