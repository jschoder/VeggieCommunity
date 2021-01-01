<?php
namespace vc\form\validation;

class EMailValidator extends AbstractValidator
{
    public function __construct()
    {
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        $formValue = $this->getValue($formElement->getName(), $formValues);
        if (!empty($formValue)) {
            if (!isEMailValid($formValue)) {
                return array(gettext('edit.validation.mail.invalid'));
            }
        }
    }
}
