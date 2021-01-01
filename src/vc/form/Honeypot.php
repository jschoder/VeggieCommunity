<?php
namespace vc\form;

class Honeypot extends FormElement
{

    public function __construct()
    {
        parent::__construct(null, null);
    }

    protected function getRenderTemplate()
    {
        return 'honeypot';
    }

    protected function getRenderParams()
    {
        return array(
            'class' => $this->getClass()
        );
    }

    /**
     * Updating the name AFTER all the elements have been added
     */
    public function updateName()
    {
        $fieldNames = array(
            'website',
            'homepage',
            'url',
            'mail',
            'name',
            'email',
            'body',
            'subject',
            'firstname',
            'lastname'
        );
        do {
            $name = $fieldNames[array_rand($fieldNames)];
        } while ($this->getForm()->hasChild($name));

        $this->setName($name);
    }

    public function setDefaultValues($defaultFormValues)
    {
    }

    public function getObjectValue(&$object, $formValues)
    {
    }

    public function setObjectValue($object)
    {
    }

    public function __toString()
    {
        return $this->getName();
    }
}
