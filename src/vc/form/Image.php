<?php
namespace vc\form;

class Image extends File
{
    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);
        if (!empty($formValues[$this->getName()]['tmp_name']) &&
           is_uploaded_file($formValues[$this->getName()]['tmp_name']) &&
           $formValues[$this->getName()]['type'] !== 'image/pjpeg' &&
           $formValues[$this->getName()]['type'] !== 'image/jpeg' &&
           $formValues[$this->getName()]['type'] !== 'image/gif' &&
           $formValues[$this->getName()]['type'] !== 'image/png') {
            \vc\lib\ErrorHandler::warning(
                'Invalid picture type ' . $formValues[$this->getName()]['type'] . ' for ' . $this->getName(),
                __FILE__,
                __LINE__,
                array(
                    'formValue' => $formValues[$this->getName()]
                )
            );
            $isValid = false;
            $this->validationErrors[] = gettext('form.fileNotPicture');
        }
        return $isValid;
    }
}
