import axios from '../api/axiosInstance';

// Attachments — backend AttachmentController. `type` is an App\Support\RecordTypes key ('user', 'product' …).
// Attachment = { id, name, mime_type, size, uploaded_by, created_at }
// - list(type, id) → { data: [Attachment] }                         (<type>-list)
// - upload(type, id, file, onProgress) → 201 { data: Attachment }    (<type>-edit) — 422 too big / wrong type
// - download(attachmentId) → the file (blob)                         (<type>-list)
// - remove(attachmentId) → { message }                               (<type>-edit)
const attachmentApi = {
  list: (type, id) => axios.get(`/attachments/${type}/${id}`),
  upload: (type, id, file, onProgress) => {
    const body = new FormData();
    body.append('file', file);
    return axios.post(`/attachments/${type}/${id}`, body, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => onProgress?.(e.total ? Math.round((e.loaded / e.total) * 100) : 0),
    });
  },
  download: (attachmentId) => axios.get(`/attachments/${attachmentId}/download`, { responseType: 'blob' }),
  remove: (attachmentId) => axios.delete(`/attachments/${attachmentId}`),
};

export default attachmentApi;
