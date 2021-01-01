<?php
namespace vc\form\validation;

class DatePeriodValidator extends AbstractValidator
{
    private $fromTime;
    private $toTime;

    public function __construct($fromTime, $toTime)
    {
        $this->fromTime = $fromTime;
        $this->toTime = $toTime;
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
        if (!empty($formValue)) {
            $time = strtotime($formElement->getValue($formValue));
            if ($time < $this->fromTime || $time > $this->toTime) {
                return array(gettext('form.validator.datePeriod'));
            }
        }
    }
}
