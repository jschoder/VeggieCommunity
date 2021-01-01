<?php
namespace vc\form;

class AjaxImage extends FormElement
{
    private $rootPath;

    private $caption;

    private $maxSize;

    public function __construct($objectField, $name, $rootPath, $caption, $maxSize = null)
    {
        parent::__construct($objectField, $name);
        $this->rootPath = $rootPath;
        $this->caption = $caption;
        if ($maxSize === null) {
            $this->maxSize = \vc\config\Globals::MAX_UPLOAD_FILE_SIZE;
        } else {
            $this->maxSize = $maxSize;
        }
    }

    protected function getRenderTemplate()
    {
        return 'ajaximage';
    }

    protected function getRenderParams()
    {
        return array(
            'rootPath' => $this->rootPath,
            'caption' => $this->caption,
            'maxSize' => $this->maxSize,
            'class' => $this->getClass(),
            'default' => $this->getDefault()
        );
    }
}
