<?php
namespace vc\form;

class Hidden extends FormElement
{
    private $value;

    public function __construct($objectField, $name, $value)
    {
        parent::__construct($objectField, $name);
        $this->value = $value;
    }

    protected function getRenderTemplate()
    {
        return 'hidden';
    }

    protected function getRenderParams()
    {
        return array(
            'class' => $this->getClass(),
            'value' => $this->value
        );
    }
}
