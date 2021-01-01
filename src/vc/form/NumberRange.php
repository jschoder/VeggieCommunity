<?php
namespace vc\form;

class NumberRange extends FormElement
{
    private $fromObjectField;

    private $toObjectField;

    private $caption;

    private $minimum;

    private $maximum;

    private $help;

    public function __construct($fromObjectField, $toObjectField, $name, $caption, $minimum, $maximum, $help = null)
    {
        parent::__construct(null, $name);
        $this->fromObjectField = $fromObjectField;
        $this->toObjectField = $toObjectField;
        $this->caption = $caption;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->help = $help;
    }

    protected function getRenderTemplate()
    {
        return 'numberrange';
    }

    public function getObjectValue(&$object, $formValues)
    {
        if (array_key_exists($this->getName(), $formValues)) {
            $from = intval($formValues[$this->getName()]['from']);
            $to = intval($formValues[$this->getName()]['to']);
            $object->{$this->fromObjectField} = min($from, $to);
            $object->{$this->toObjectField} = max($from, $to);
        }
    }

    public function setObjectValue($object)
    {
        if (isset($object->{$this->fromObjectField}) || isset($object->{$this->toObjectField})) {
            $default = array();
            if (isset($object->{$this->fromObjectField})) {
                $default['from'] = $object->{$this->fromObjectField};
            }
            if (isset($object->{$this->toObjectField})) {
                $default['to'] = $object->{$this->toObjectField};
            }
            $this->setDefault($default);
        }
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'class' => $this->getClass(),
                     'minimum' => $this->minimum,
                     'maximum' => $this->maximum,
                     'default' => $this->getDefault(),
                     'help' => $this->help);
    }
}
