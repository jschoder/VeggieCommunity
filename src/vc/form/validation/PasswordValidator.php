<?php
namespace vc\form\validation;

class PasswordValidator extends AbstractValidator
{
    private $validChars;

    public function __construct($validChars)
    {
        $this->validChars = $validChars;
    }

    public function validate($db, \vc\form\FormElement $formElement, array $formValues)
    {
        if (!empty($formValues[$formElement->getName()][0])) {
            if (!$this->isPasswordValid($formValues[$formElement->getName()][0])) {
                return array(
                    gettext("edit.validation.invalidcharacters") . ' ' .
                    implode(' ', $this->validChars)
                );
            }
        }
    }

    private function isPasswordValid($password)
    {
        $normalChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789 ';
        $validChars = array_merge($this->validChars, str_split($normalChars));
        $length = mb_strlen($password);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($password, $i, 1);
            if (!in_array($char, $validChars)) {
                return false;
            }
        }
        return true;
    }
}
