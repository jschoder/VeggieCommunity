<?php
namespace vc\form;

class Form
{
    private $id;

    private $name;

    private $path;

    private $locale;

    private $target;

    private $method;

    private $honeypot;

    private $children = array();

    public function __construct($id, $name, $path, $locale, $target, $honeyPotProbability = 0.8, $method = 'post')
    {
        $this->id = $id;
        $this->name = $name;
        $this->path = $path;
        $this->locale = $locale;
        $this->target = $target;
        $this->method = $method;
        $this->add(new Hidden(null, 'formid', $id));
        if ($honeyPotProbability == 1 || rand(0, 99) < $honeyPotProbability * 100) {
            $this->honeypot = new Honeypot();
            $this->add($this->honeypot);
        }
    }

    public function add(FormElement $child)
    {
        $child->setForm($this);
        $this->children[] = $child;
        return $child;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function isMultipart()
    {
        foreach ($this->children as $child) {
            if ($child->isMultipart()) {
                return true;
            }
        }
        return false;
    }

    public function render(\vc\view\html\View $view)
    {
        $content = '';
        foreach ($this->children as $child) {
            $content .= $child->render($view);
        }
        return $view->element(
            'form/form',
            array(
                'id' => $this->id,
                'name' => $this->name,
                'path' => $this->path,
                'locale' => $this->locale,
                'target' => $this->target,
                'method' => $this->method,
                'content' => $content,
                'isMultipart' => $this->isMultipart()
            )
        );
    }

    public function validate($db, $formValues)
    {
        $isValid = true;
        foreach ($this->children as $child) {
            $isValid = $isValid && $child->validate($db, $formValues);
        }
        return $isValid;
    }

    public function getObject($object, $formValues)
    {
        foreach ($this->children as $child) {
            $child->getObjectValue($object, $formValues);
        }
        return $object;
    }

    public function setObject($object)
    {
        foreach ($this->children as $child) {
            $child->setObjectValue($object);
        }
    }

    public function setDefaultValues($defaultFormValues)
    {
        foreach ($this->children as &$child) {
            $child->setDefaultValues($defaultFormValues);
        }
    }

    public function getChildren()
    {
        return $this->children;
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

    public function updateHoneypot()
    {
        if (!empty($this->honeypot)) {
            $this->honeypot->updateName();
        }
    }

    public function getHoneypot()
    {
        return $this->honeypot;
    }

    public function somethingInHoneytrap($formValues)
    {
        if (!empty($this->honeypot) &&
            !empty($formValues[$this->honeypot->getName()])) {
            return true;
        } else {
            return false;
        }
    }
}
