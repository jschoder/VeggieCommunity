<?php
namespace vc\form\validation;

class SelectItemValidator extends AbstractValidator
{
    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        if (!($formElement instanceof \vc\form\Select)) {
            \vc\lib\ErrorHandler::error(
                'Invalid class for validator ' . get_class($formElement),
                __FILE__,
                __LINE__,
                array('formValues' => $formValues)
            );
        } else {
            $formValue = $this->getValue($formElement->getName(), $formValues);
            if (!array_key_exists($formValue, $formElement->getOptions())) {
                \vc\lib\ErrorHandler::error(
                    'Validation of select items failed.',
                    __FILE__,
                    __LINE__,
                    array(
                        'formValue' => $formValue,
                        'options' => $formElement->getOptions(),
                        'formElement' => $formElement,
                        'formValues' => $formValues
                    )
                );
                throw new \vc\exception\FatalSystemException();
            }
        }
    }
}



