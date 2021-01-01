<?php
namespace vc\form\validation;

class MinLengthValidator extends AbstractValidator
{
    private $minLength;

    public function __construct($minLength)
    {
        $this->minLength = $minLength;
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
                    if (mb_strlen($subfield) < $this->minLength) {
                        return array(str_replace(
                            '%LENGTH%',
                            $this->minLength,
                            gettext('form.validator.minlength.tooshort')
                        ));
                    }
                }
            } else {
                $formValue = trim($formValues[$formElement->getName()]);
                if (mb_strlen($formValue) < $this->minLength) {
                    return array(str_replace(
                        '%LENGTH%',
                        $this->minLength,
                        gettext('form.validator.minlength.tooshort')
                    ));
                }
            }
        }

        return null;
    }
}
