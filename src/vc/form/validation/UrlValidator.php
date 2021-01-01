<?php
namespace vc\form\validation;

class UrlValidator extends AbstractValidator
{
    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        $formValue = $this->getValue($formElement->getName(), $formValues);
        if (!empty($formValue)) {
            if (filter_var($formValue, FILTER_VALIDATE_URL) === false &&
                filter_var('http://' . $formValue, FILTER_VALIDATE_URL) === false) {
                return array(gettext('edit.validation.invalidurl'));
            }
        }
    }
}
