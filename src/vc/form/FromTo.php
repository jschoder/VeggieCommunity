<?php
namespace vc\form;

class FromTo extends FormElement
{
    private $caption;

    private $options;

    private $fromText;

    private $toText;

    private $help;

    public function __construct($objectField, $name, $caption, $options, $fromText = null, $toText = null, $help = null)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->options = $options;
        $this->fromText = $fromText;
        $this->toText = $toText;
        $this->help = $help;
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if ($isValid &&
            !empty($formValues[$this->getName()])) {
            $value = $formValues[$this->getName()];

            // The component works on the presumption that the developer
            // either uses only indexed array (1=>'abc', 2=>'def')
            // or or numeric arrays (1,2,3,4,5)
            if (is_numeric(current($this->options))) {
                // Using the arrayValues as value source
                if (!in_array($value, $this->options)) {
                    $isValid = false;
                }
            } else {
                // Using the arrayKeys as value source
                // Using the arrayValues as value source
                if (!array_key_exists($value, $this->options)) {
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    protected function getRenderTemplate()
    {
        return 'fromto';
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'options' => $this->options,
                     'fromText' => $this->fromText,
                     'toText' => $this->toText,
                     'default' => $this->getDefault(),
                     'class' => $this->getClass(),
                     'help' => $this->help);
    }
}
