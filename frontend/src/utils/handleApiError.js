// src/utils/handleApiError.js
//
// Use in every catch block that talks to the API:
//   catch (error) { handleApiError(error, message) }   // message = App.useApp().message
//
// - 422 { message, errors } (Laravel validation) or 422 { message } (a
//   business rule — BusinessRuleException): shows the message
// - AntD form validation (errorFields): nothing — the form shows them inline
// - anything else: the API's message, or a generic one

const handleApiError = (error, messageApi) => {
  if (error?.errorFields) return;

  const data = error?.response?.data;
  if (error?.response?.status === 422) {
    const first = data?.errors ? Object.values(data.errors)[0] : null;
    messageApi.error(data?.message || (Array.isArray(first) ? first[0] : first) || 'Please check the form.');
    return;
  }
  if (!error?.response && error?.code !== 'ERR_CANCELED') {
    messageApi.error('The server can\'t be reached — check your connection.');
    return;
  }
  messageApi.error(data?.message || 'Something went wrong.');
};

export default handleApiError;
