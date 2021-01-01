<?php
namespace vc\form;

abstract class FormElement
{
    private $form = null;

    private $parentElement = null;

    private $objectField;

    private $id;

    private $name;

    private $arrayIndex = null;

    private $class = '';

    private $mandatory = false;

    private $validators = array();

    private $default = null;

    private $small = false;

    protected $validationErrors = array();

    public function __construct($objectField, $name)
    {
        $this->objectField = $objectField;
        $this->name = $name;
    }

    public function getParentElement()
    {
        return $this->parentElement;
    }

    public function setParentElement($parentElement)
    {
        $this->parentElement = $parentElement;
    }

    public function getObjectField()
    {
        return $this->objectField;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getArrayIndex()
    {
        return $this->arrayIndex;
    }

    public function setArrayIndex($arrayIndex)
    {
        $this->arrayIndex = $arrayIndex;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function isMandatory()
    {
        return $this->mandatory;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function setDefaultValues($defaultFormValues)
    {
        $childName = $this->getName();
        if ($childName != 'formid' &&
            array_key_exists($childName, $defaultFormValues)) {
            $this->default = $defaultFormValues[$childName];
        }
        return $this;
    }

    public function setSmall($small)
    {
        $this->small = $small;
        return $this;
    }

    abstract protected function getRenderTemplate();

    abstract protected function getRenderParams();

    public function render(\vc\view\html\View $view)
    {
        $renderParams = $this->getRenderParams();
        $renderParams['id'] = $this->getRenderId();
        $renderParams['name'] = $this->getRenderName();
        $renderParams['form'] = $this->form;
        $renderParams['locale'] = $this->form->getLocale();
        $renderParams['mandatory'] = $this->mandatory;
        $renderParams['validationErrors'] = $this->validationErrors;
        if ($this->small) {
            return $view->element('form/' . $this->getRenderTemplate() . '.small', $renderParams);
        } else {
            return $view->element('form/' . $this->getRenderTemplate(), $renderParams);
        }
    }

    protected function getRenderId()
    {
        if ($this->id === null) {
            $id = $this->form->getName() . '-' . str_replace(array('[', ']'), array('-', ''), strtolower($this->name));
        } else {
            $id = $this->id;
        }
        if ($this->arrayIndex !== null) {
            $id .= '-' . $this->arrayIndex;
        }
        return $id;
    }

    public function getRenderName()
    {
        if ($this->name === null) {
            return null;
        } else {
            $name = $this->name;
            $nameIndex = '';
            $parentElement = $this->getParentElement();
            while ($parentElement !== null) {
                $parentName = $parentElement->getName();
                if (!empty($parentName)) {
                    $nameIndex = '[' . $name . ']' . $nameIndex;
                    $name = $parentName;
                }
                $parentElement = $parentElement->getParentElement();
            }

            if ($this->arrayIndex === null) {
                return $name . $nameIndex;
            } elseif ($this->arrayIndex === '') {
                return $name  . $nameIndex . '[]';
            } else {
                return $name . '[' . $this->arrayIndex . ']' . $nameIndex;
            }
        }
    }

    public function addValidator(validation\AbstractValidator $validator)
    {
        $this->validators[] = $validator;
        return $this;
    }

    public function validate($db, $formValues)
    {
        $isValid = true;
        $this->validationErrors = array();

        if (empty($formValues[$this->getName()])) {
            $formValue = '';
        } else {
            $formValue = $formValues[$this->getName()];
            if (is_string($formValue)) {
                $formValue = trim($formValue);
            }
        }

        if ($this->mandatory && empty($formValue)) {
            $isValid = false;
            $this->validationErrors[] = gettext('form.missingMandatory');
        } else {
            foreach ($this->validators as $validator) {
                $validationErrors = $validator->validate($db, $this, $formValues);
                if (is_array($validationErrors)) {
                    $isValid = false;
                    $this->validationErrors = array_merge($this->validationErrors, $validationErrors);
                }
            }
        }
        return $isValid;
    }

    public function isMultipart()
    {
        return false;
    }

    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                $formValue = $formValues[$this->getName()];
                if (is_string($formValue)) {
                    $formValue = trim($formValue);
                } elseif (is_array($formValue)) {
                    foreach ($formValue as $key => $value) {
                        if (is_string($value)) {
                            $formValue[$key] = trim($value);
                        }
                    }
                }
                $object->$objectField = $formValue;
            }
        }
    }

    public function setObjectValue($object)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null && isset($object->$objectField)) {
            $this->setDefault($object->$objectField);
        }
    }
}
