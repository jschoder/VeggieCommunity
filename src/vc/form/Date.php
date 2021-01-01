<?php
namespace vc\form;

class Date extends FormElement
{
    private $caption;

    private $minDate;

    private $maxDate;

    private $time;

    private $help;

    private $invalidMaxDate;

    public function __construct(
        $objectField,
        $name,
        $caption,
        $minDate,
        $maxDate,
        $time = false,
        $help = null,
        $invalidMaxDate = null
    ) {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
        $this->time = $time;
        $this->help = $help;
        $this->invalidMaxDate = $invalidMaxDate;

        $this->addValidator(new \vc\form\validation\DatePeriodValidator($minDate, $maxDate));
    }

    protected function getRenderTemplate()
    {
        return 'date';
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'minDate' => $this->minDate,
                     'maxDate' => $this->invalidMaxDate === null ? $this->maxDate : $this->invalidMaxDate,
                     'default' => $this->getDefault(),
                     'class' => $this->getClass(),
                     'time' => $this->time,
                     'help' => $this->help);
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function isTime()
    {
        return $this->time;
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if ($isValid) {
            if ($this->isMandatory()) {
                if (empty($formValues[$this->getName()]['day']) ||
                    empty($formValues[$this->getName()]['month']) ||
                    empty($formValues[$this->getName()]['year'])) {
                    $isValid = false;
                    $this->validationErrors[] = gettext('form.missingMandatory');
                } elseif ($this->time && empty($formValues[$this->getName()]['time'])) {
                    $isValid = false;
                    $this->validationErrors[] = gettext('form.missingMandatory');
                }
            }

            if (!empty($formValues[$this->getName()]['day']) &&
                !empty($formValues[$this->getName()]['month']) &&
                !empty($formValues[$this->getName()]['year'])) {

                if (!checkdate(
                    intval($formValues[$this->getName()]['month']),
                    intval($formValues[$this->getName()]['day']),
                    intval($formValues[$this->getName()]['year'])
                )) {
                    $isValid = false;
                    $this->validationErrors[] = gettext('form.invalidDateFormat');
                }
            }

            if ($this->time && !empty($formValues[$this->getName()]['time'])) {
                $dateTime = \DateTime::createFromFormat(
                    'H:i',
                    $formValues[$this->getName()]['time']
                );
                if ($dateTime === false) {
                    $isValid = false;
                    $this->validationErrors[] = gettext('form.invalidTimeFormat');
                }
            }
        }
        return $isValid;
    }

    public function getValue($formValue)
    {
        $date = strtotime(
            intval($formValue['year']) . '-' .
            intval($formValue['month']) . '-' .
            intval($formValue['day'])
        );
        if ($date === false) {
            return null;
        }

        if (empty($formValue['time'])) {
            return date('Y-m-d 00:00:00', $date);
        } else {
            return date('Y-m-d', $date) . ' ' . trim($formValue['time']) . ':00';
        }
    }

    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                $formValue = $formValues[$this->getName()];
                if (!empty($formValue['day']) && !empty($formValue['month']) && !empty($formValue['year'])) {
                    $object->$objectField = $this->getValue($formValue);
                }
            }
        }
    }

    public function setObjectValue($object)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null && isset($object->$objectField)) {
            $time = strtotime($object->$objectField);
            $default = array(
                'day' => date('d', $time),
                'month' => date('m', $time),
                'year' => date('Y', $time),
                'time' => date('H:i', $time)
            );
            $this->setDefault($default);
        }
    }
}
