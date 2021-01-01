<?php
namespace vc\form;

class Submit extends FormElement
{
    private $caption;
    private $primaryName = null;
    private $secondaryButtons = null;

    public function __construct($caption, $primaryName = null, $secondaryButtons = null)
    {
        parent::__construct(null, null);
        $this->caption = $caption;
        $this->primaryName = $primaryName;
        $this->secondaryButtons = $secondaryButtons;
    }

    protected function getRenderTemplate()
    {
        return 'submit';
    }

    protected function getRenderParams()
    {
        return array(
            'caption' => $this->caption,
            'class' => $this->getClass(),
            'primaryName' => $this->primaryName,
            'secondaryButtons' => $this->secondaryButtons
        );
    }
}
