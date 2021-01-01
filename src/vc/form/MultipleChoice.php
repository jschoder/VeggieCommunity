<?php
namespace vc\form;

class MultipleChoice extends FormElement
{
    private $caption;

    private $options;

    private $help;
    
    private $minCount;

    private $maxCount;
    
    private $columns = false;

    public function __construct($objectField, $name, $caption, $options, $help = null, $minCount = 0, $maxCount = null)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->options = $options;
        $this->help = $help;
        $this->minCount = $minCount;
        $this->maxCount = $maxCount;
    }

    protected function getRenderTemplate()
    {
        return 'multiplechoice';
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'options' => $this->options,
                     'class' => $this->getClass(),
                     'default' => $this->getDefault(),
                     'columns' => $this->columns,
                     'help' => $this->help,
                     'minCount' => $this->minCount,
                     'maxCount' => $this->maxCount);
    }
    
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }
}
