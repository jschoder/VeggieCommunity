<?php
namespace vc\form;

class Text extends FormElement
{
    const TEXTFIELD = 1;
    const PASSWORD = 2;
    const TEXTAREA = 3;

    private $caption;

    private $maxlength;

    private $help;

    private $type;

    private $readonly = false;

    public function __construct($objectField, $name, $caption, $maxlength, $help = null, $type = null)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->maxlength = $maxlength;
        $this->help = $help;
        $this->type = $type;

        if ($maxlength !== null) {
            $this->addValidator(new validation\MaxLengthValidator($maxlength));
        }
    }

    protected function getRenderTemplate()
    {
        if ($this->type == self::TEXTAREA) {
            return 'textarea';
        } else {
            return 'textfield';
        }
    }

    protected function getRenderParams()
    {
        $params = array(
            'caption' => $this->caption,
            'class' => $this->getClass(),
            'readonly' => $this->readonly,
            'maxlength' => $this->maxlength,
            'default' => $this->getDefault(),
            'help' => $this->help
        );
        if ($this->type == self::TEXTFIELD || $this->type == null) {
            $params['type'] = 'text';
        } elseif ($this->type == self::PASSWORD) {
            $params['type'] = 'password';
            $params['default'] = null;
        }
        return $params;
    }

    public function setReadonly($readonly = true)
    {
        $this->readonly = $readonly;
        return $this;
    }
}
