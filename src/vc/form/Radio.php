<?php
namespace vc\form;

class Radio extends FormElement
{
    private $caption;

    private $selected = false;

    public function __construct($objectField, $name, $caption)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
    }

    protected function getRenderTemplate()
    {
        return 'radio';
    }

    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'class' => $this->getClass(),
                     'default' => $this->getDefault(),
                     'selected' => $this->selected);
    }
}
