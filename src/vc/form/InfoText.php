<?php
namespace vc\form;

class InfoText extends FormElement
{
    private $notifyType;

    private $text;

    public function __construct($notifyType, $text)
    {
        parent::__construct(null, null);
        $this->notifyType = $notifyType;
        $this->text = $text;
    }

    protected function getRenderTemplate()
    {
        return 'infotext';
    }

    protected function getRenderParams()
    {
        if (is_array($this->text)) {
            if (array_key_exists($this->getArrayIndex(), $this->text)) {
                $text = $this->text[$this->getArrayIndex()];
            } else {
                $text = '';
            }
        } else {
            $text = $this->text;
        }
        return array(
            'notifyType' => $this->notifyType,
            'text' => $text
        );
    }
}
