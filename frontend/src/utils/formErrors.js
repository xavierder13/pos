// src/utils/formErrors.js
//
// Put a Laravel 422's field errors ({ message, errors: { field: [..] } }) on
// the AntD form's fields. Returns true when it did — otherwise fall back to
// handleApiError. Nested keys ("roles.0") go on their list field ("roles").
//
//   catch (error) { if (!applyFormErrors(form, error)) handleApiError(error, message) }

const applyFormErrors = (form, error) => {
  const errors = error?.response?.status === 422 ? error.response.data?.errors : null;
  if (!errors) return false;

  const byField = {};
  Object.entries(errors).forEach(([key, messages]) => {
    const name = key.split('.')[0];
    byField[name] = [...(byField[name] || []), ...[].concat(messages)];
  });
  form.setFields(Object.entries(byField).map(([name, fieldErrors]) => ({ name, errors: fieldErrors })));
  return true;
};

export default applyFormErrors;
