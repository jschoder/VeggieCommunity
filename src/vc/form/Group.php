<?php
namespace vc\form;

class Group extends FormElement
{
    private $caption;

    private $children = array();

    private $collapsed;

    private $childrenContent = array();

    private $columns;

    public function __construct($caption, $collapsed = false, $columns = 1)
    {
        parent::__construct(null, null);
        $this->caption = $caption;
        $this->collapsed = $collapsed;
        $this->columns = $columns;
    }

    protected function getRenderTemplate()
    {
        return 'group';
    }

    /**
     *
     * @param \vc\form\FormElement $child
     * @return \vc\form\FormElement
     */
    public function add(FormElement &$child)
    {
        $child->setForm($this->getForm());
        $child->setParentElement($this);
        $this->children[] = $child;
        return $child;
    }

    public function render(\vc\view\html\View $view)
    {
        $this->childrenContent = array();
        $chunkedChildren = array_chunk($this->children, ceil(count($this->children) / $this->columns));
        foreach ($chunkedChildren as $children) {
            $childrenContent = '';
            foreach ($children as $child) {
                $childrenContent .= $child->render($view, 'row');
            }
            $this->childrenContent[] = $childrenContent;
        }
        return parent::render($view);
    }

    protected function getRenderParams()
    {
        return array(
            'caption' => $this->caption,
            'collapsed' => $this->collapsed,
            'class' => $this->getClass(),
            'columns' => $this->columns,
            'childrenContent' => $this->childrenContent
        );
    }

    public function setObjectValue($object)
    {
        foreach ($this->children as $child) {
            $child->setObjectValue($object);
        }
    }

    public function setDefaultValues($defaultFormValues)
    {
        foreach ($this->children as $child) {
            $child->setDefaultValues($defaultFormValues);
        }
    }

    public function getObjectValue(&$object, $formValues)
    {
        foreach ($this->children as $child) {
            $child->getObjectValue($object, $formValues);
        }
    }

    public function validate($db, $formValues)
    {
        $isValid = true;
        foreach ($this->children as $child) {
            $isValid = $isValid && $child->validate($db, $formValues);
        }
        return $isValid;
    }

    public function hasChild($name)
    {
        foreach ($this->children as &$child) {
            if ($child instanceof Group) {
                if ($child->hasChild($name)) {
                    return true;
                }
            } elseif ($child->getName() == $name) {
                return true;
            }
        }
        return false;
    }
}
