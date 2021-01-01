<?php
namespace vc\form\validation;

abstract class AbstractValidator
{
    protected function getValue($elementName, array $formValues)
    {
        if (empty($formValues[$elementName])) {
            return '';
        } else {
            $formValue = $formValues[$elementName];
            if (is_string($formValue)) {
                $formValue = trim($formValue);
            }
            return $formValue;
        }
    }

    abstract public function validate($db, \vc\form\FormElement $formElement, array $formValues);
}
