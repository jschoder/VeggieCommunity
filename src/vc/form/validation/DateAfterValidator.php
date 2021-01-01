<?php
namespace vc\form\validation;

class DateAfterValidator extends AbstractValidator
{
    private $beforeDateElement;

    public function __construct($beforeDateElement)
    {
        $this->beforeDateElement = $beforeDateElement;
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        if (!($formElement instanceof \vc\form\Date)) {
            \vc\lib\ErrorHandler::error(
                'Invalid class for validator ' . get_class($formElement),
                __FILE__,
                __LINE__,
                array('formValues' => $formValues)
            );
        }

        $formValue = $this->getValue($formElement->getName(), $formValues);
        $formBeforeValue = $this->getValue($this->beforeDateElement->getName(), $formValues);
        if (!empty($formValue) && !empty($formBeforeValue)) {
            $fromDate = $this->beforeDateElement->getValue($formValues[$this->beforeDateElement->getName()]);
            $toDate = $formElement->getValue($formValues[$formElement->getName()]);
            if (strtotime($fromDate) >= strtotime($toDate)) {
                return array(str_replace(
                    '%FIELD%',
                    $this->beforeDateElement->getCaption(),
                    gettext('form.validator.dateAfter')
                ));
            }
        }
    }
}
