<?php
namespace vc\form;

class Password extends FormElement
{
    private $caption;

    private $repeatCaption;

    private $maxlength;

    private $validChars;

    public function __construct($objectField, $name, $caption, $repeatCaption, $maxlength, $validChars)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->repeatCaption = $repeatCaption;
        $this->maxlength = $maxlength;
        $this->validChars = $validChars;

        if ($maxlength !== null) {
            $this->addValidator(new validation\MaxLengthValidator($maxlength));
        }

        $this->addValidator(new validation\PasswordValidator($validChars));
    }

    protected function getRenderTemplate()
    {
        return 'password';
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'repeatCaption' => $this->repeatCaption,
                     'class' => $this->getClass(),
                     'maxlength' => $this->maxlength,
                     'validChars' => $this->validChars);
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if ($isValid) {
            if ($formValues[$this->getName()][0] != $formValues[$this->getName()][1]) {
                $isValid = false;
                $this->validationErrors[] = gettext('form.differentPasswords');
            }
        }
        return $isValid;
    }

    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                // Don't trim passwords. Spaces at the beginning or end of a password are totally allowed
                $object->$objectField = $formValues[$this->getName()][0];
            }
        }
    }
}
