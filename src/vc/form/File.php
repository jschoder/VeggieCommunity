<?php
namespace vc\form;

class File extends FormElement
{
    private $caption;

    private $help;

    private $maxSize;

    public function __construct($objectField, $name, $caption, $help, $maxSize = null)
    {
        parent::__construct($objectField, $name);
        $this->caption = $caption;
        $this->help = $help;
        if ($maxSize === null) {
            $this->maxSize = \vc\config\Globals::MAX_UPLOAD_FILE_SIZE;
        } else {
            $this->maxSize = $maxSize;
        }
    }

    protected function getRenderTemplate()
    {
        return 'file';
    }

    protected function getRenderParams()
    {
        return array('caption' => $this->caption,
                     'class' => $this->getClass(),
                     'help' => $this->help,
                     'maxSize' => $this->maxSize);
    }

    public function isMultipart()
    {
        return true;
    }

    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if (!$this->isMandatory() &&
           empty($formValues[$this->getName()]['tmp_name'])) {
            return true;
        } elseif (empty($formValues[$this->getName()]['tmp_name']) ||
           !is_uploaded_file($formValues[$this->getName()]['tmp_name'])) {
            $isValid = false;
            $this->validationErrors[] = gettext('form.fileNotUploaded');
        } else {
            $filesize = filesize($formValues[$this->getName()]['tmp_name']);
            if ($filesize > $this->maxSize) {
                $isValid = false;
                $this->validationErrors[] = gettext('form.fileTooBig');
            }
        }
        return $isValid;
    }


    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                $formValue = $formValues[$this->getName()];
                // Skip empty files
                if (!is_array($formValue) ||
                    !array_key_exists('tmp_name', $formValue) ||
                    !empty($formValue['tmp_name'])) {
                    $object->$objectField = $formValue;
                }
            }
        }
    }

    public function setObjectValue($object)
    {
        // Can't preset a file
    }
}
