<?php
namespace vc\form;

class Select extends FormElement
{
    private $caption;

    private $options;

    private $help;

    private $radios;

    private $groups = null;

    private $filterField = null;

    private $filterGroups = array();

    public function __construct($objectField, $name, $caption, $options, $help = null, $radios = false)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->options = $options;
        $this->help = $help;
        $this->radios = $radios;

        $this->addValidator(new validation\SelectItemValidator());
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function setFilterField($filterField)
    {
        $this->filterField = $filterField;
        return $this;
    }

    public function setFilterGroup($value, $groupId)
    {
        $this->filterGroups[$value] = $groupId;
    }

    protected function getRenderTemplate()
    {
        if ($this->radios) {
            return 'select.radios';
        } else {
            return 'select';
        }
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'class' => $this->getClass(),
                     'options' => $this->options,
                     'default' => $this->getDefault(),
                     'groups' => $this->groups,
                     'filterField' => $this->filterField,
                     'filterGroups' => $this->filterGroups);
    }
}
