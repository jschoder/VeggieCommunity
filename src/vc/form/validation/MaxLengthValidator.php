<?php
namespace vc\form\validation;

class MaxLengthValidator extends AbstractValidator
{
    private $maxLength;

    public function __construct($maxLength)
    {
        $this->maxLength = $maxLength;
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
                    if (mb_strlen($subfield) > $this->maxLength) {
                        return array(str_replace(
                            '%LENGTH%',
                            $this->maxLength,
                            gettext('form.validator.maxlength.toolong')
                        ));
                    }
                }
            } else {
                $formValue = trim($formValues[$formElement->getName()]);
                if (mb_strlen($formValue) > $this->maxLength) {
                    return array(str_replace(
                        '%LENGTH%',
                        $this->maxLength,
                        gettext('form.validator.maxlength.toolong')
                    ));
                }
            }
        }
    }
}
