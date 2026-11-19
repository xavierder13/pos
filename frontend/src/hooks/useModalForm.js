// src/hooks/useModalForm.js
//
// Every add / edit dialog's form: the fields are EMPTY again whenever the
// dialog closes — ×, Cancel, or after saving — so the next Add never shows
// the record viewed before.
//
//   const { form, modalProps, formProps } = useModalForm();
//   <Modal open={open} keyboard={false} {...modalProps}>
//     <Form {...formProps} initialValues={record ? {...} : {...}}>
//
// The Modal unmounts its content when hidden (destroyOnHidden) and the Form
// drops each field's value as it unmounts (preserve={false}). Never call
// form.resetFields() on close — it restores the LAST initialValues.

import { Form } from 'antd';

const useModalForm = () => {
  const [form] = Form.useForm();

  return {
    form,
    modalProps: { destroyOnHidden: true },
    formProps: { form, preserve: false },
  };
};

export default useModalForm;
