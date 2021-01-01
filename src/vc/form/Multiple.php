<?php
namespace vc\form;

class Multiple extends FormElement
{
    private $caption;

    private $children = array();

    private $count;

    private $emptyChildrenContent = array();

    private $filledChildrenContent = array();

    private $deleteCaption = null;

    private $deleteCheckboxName = null;

    private $sortable = false;

    public function __construct($name, $caption, $count, $deleteCaption = null)
    {
        parent::__construct(null, $name);
        $this->caption = $caption;
        $this->count = $count;
        $this->deleteCaption = $deleteCaption;
    }

    protected function getRenderTemplate()
    {
        return 'multiple';
    }

    public function add(FormElement &$child)
    {
        $child->setForm($this->getForm());
        $child->setParentElement($this);
        $this->children[] = $child;
        return $child;
    }

    public function setSortable()
    {
        $this->sortable = true;
        $this->add(new \vc\form\Hidden(
            $this->getName() . 'weight',
            'weight',
            null
        ))->setClass('jWeight');
        return $this;
    }

    public function render(\vc\view\html\View $view)
    {
        $idBase = $this->getForm()->getName() . '-' . $this->getName() . '-';

        if (!empty($this->count)) {
            for ($i = 1; $i <= $this->count; $i++) {
                $content = '';
                foreach ($this->children as $child) {
                    if ($child instanceof Radio) {
                        $child->setDefault('new-' . $i);
                    } else {
                        $child->setArrayIndex('new-' . $i);
                    }
                    $content .= $child->render($view);
                }
                $this->emptyChildrenContent[$idBase. 'new-' . $i] = $content;
            }
        }

        if ($this->deleteCaption === null) {
            $deleteCheckBox = null;
        } else {
            $deleteCheckBox = new \vc\form\Checkbox(
                $this->getName() . '.delete',
                'delete',
                $this->deleteCaption,
                true
            );
            $deleteCheckBox->setClass('deleteRow');
            $deleteCheckBox->setDefault('1');
            $this->add($deleteCheckBox);

            $this->deleteCheckboxName = $deleteCheckBox->getRenderName() . '[]';
        }

        $default = $this->getDefault();
        if (!empty($default)) {
            foreach ($default as $key => $rowValue) {
                if (is_array($rowValue) && $key !== 'delete') {
                    $contentKey = $idBase. $key;
                    $emptyContent = array_key_exists($contentKey, $this->emptyChildrenContent);
                    $content = '';
                    foreach ($this->children as $child) {
                        $childName = $child->getName();
                        if ($child === $deleteCheckBox) {
                            $child->setDefault($key);
                            $child->setArrayIndex('');
                            $child->setId($idBase. $key . '-delete');
                        } elseif ($child instanceof Radio) {
                            $child->setDefault($key);
                            $child->setSelected($default[$childName] == $key);
                        } else {
                            if (array_key_exists($childName, $rowValue)) {
                                $child->setDefault($rowValue[$childName]);
                            }
                            $child->setArrayIndex($key);
                        }

                        if ($child !== $deleteCheckBox || !$emptyContent) {
                            $content .= $child->render($view, 'row');
                        }
                    }

                    // This happens on a submitted multiple that has a validation error
                    if ($emptyContent) {
                        $this->emptyChildrenContent[$contentKey] = $content;
                    } else {
                        $this->filledChildrenContent[$contentKey] = $content;
                    }
                }
            }
        }
        return parent::render($view);
    }

    protected function getRenderParams()
    {
        return array(
            'sortable' => $this->sortable,
            'caption' => $this->caption,
            'class' => $this->getClass(),
            'count' => $this->count,
            'filledChildrenContent' => $this->filledChildrenContent,
            'emptyChildrenContent' => $this->emptyChildrenContent,
            'deleteCheckboxName' => $this->deleteCheckboxName
        );
    }
}
