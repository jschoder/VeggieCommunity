<?php
namespace vc\form;

class Checkbox extends FormElement
{
    private $caption;

    private $isRow;

    private $help;

    public function __construct($objectField, $name, $caption, $isRow = false, $help = null)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->isRow = $isRow;
        $this->help = $help;
    }

    protected function getRenderTemplate()
    {
        if ($this->isRow) {
            return 'checkbox.row';
        } else {
            return 'checkbox';
        }
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'class' => $this->getClass(),
                     'default' => $this->getDefault(),
                     'help' => $this->help);
    }

    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                $object->$objectField = 1;
            } else {
                $object->$objectField = 0;
            }
        }
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if (count($this->validationErrors) > 0 &&
            $this->validationErrors[0] == gettext('form.missingMandatory')) {
            $this->validationErrors[0] = gettext('form.missingCheckboxMandatory');
        }
        return $isValid;
    }
}
