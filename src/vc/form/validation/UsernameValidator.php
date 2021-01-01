<?php
namespace vc\form\validation;

class UsernameValidator extends AbstractValidator
{
    public function __construct()
    {
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        if (!($formElement instanceof \vc\form\Text) &&
            !($formElement instanceof \vc\form\Password)) {
            \vc\lib\ErrorHandler::error(
                'Invalid class for validator ' . get_class($formElement),
                __FILE__,
                __LINE__,
                array('formValues' => $formValues)
            );
        }

        if (!empty($formValues[$formElement->getName()])) {
            if (is_array($formValues[$formElement->getName()])) {
                foreach ($formValues[$formElement->getName()] as $subfield) {
                    $subfield = trim($subfield);
                    if (!preg_match('/[A-Z]+/', $subfield) &&
                        !preg_match('/[a-z]+/', $subfield)) {
                        return array(gettext('form.validator.username.numeric'));
                    }
                    if (strpos($subfield, '@') !== false) {
                        return array(gettext('form.validator.username.at'));
                    }
                }
            } else {
                $formValue = $this->getValue($formElement->getName(), $formValues);
                if (!preg_match('/[A-Z]+/', $formValue) &&
                    !preg_match('/[a-z]+/', $formValue)) {
                    return array(gettext('form.validator.username.numeric'));
                }
                if (strpos($formValues[$formElement->getName()], '@') !== false) {
                    return array(gettext('form.validator.username.at'));
                }
            }
        }

        return null;
    }
}
