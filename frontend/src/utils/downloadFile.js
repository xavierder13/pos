// src/utils/downloadFile.js
//
// Save an axios response fetched with `responseType: 'blob'` as a file.
// The file name comes from Content-Disposition (exposed by the API's CORS
// config), else `fallbackName`. A JSON body on a 200 (an API returning an
// error instead of the file) shows its message instead of saving a broken file.
//
//   const response = await userApi.export(params);
//   await downloadFile(response, 'users.csv', message);

const fileNameFrom = (disposition) => {
  if (!disposition) return null;
  const utf8 = /filename\*=UTF-8''([^;]+)/i.exec(disposition);
  if (utf8) return decodeURIComponent(utf8[1]);
  const plain = /filename="?([^";]+)"?/i.exec(disposition);
  return plain ? plain[1] : null;
};

const downloadFile = async (response, fallbackName, messageApi) => {
  const contentType = response.headers?.['content-type'] || '';
  if (contentType.includes('json')) {
    let text = 'The server did not return a file.';
    try {
      const parsed = JSON.parse(await response.data.text());
      text = parsed.message || parsed.error || text;
    } catch { /* not JSON either */ }
    messageApi?.error(text);
    return false;
  }

  const url = window.URL.createObjectURL(response.data);
  const link = document.createElement('a');
  link.href = url;
  link.download = fileNameFrom(response.headers?.['content-disposition']) || fallbackName;
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
  return true;
};

export default downloadFile;
