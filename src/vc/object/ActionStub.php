<?php
namespace vc\object;

class ActionStub
{
    private $id = null;

    private $class = null;

    private $title = null;

    private $caption = null;

    //-----------------------------------------------------------------------------

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    //-----------------------------------------------------------------------------

    public function getId()
    {
        return $this->id;
    }

    //-----------------------------------------------------------------------------

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    //-----------------------------------------------------------------------------

    public function getClass()
    {
        return $this->class;
    }

    //-----------------------------------------------------------------------------

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    //-----------------------------------------------------------------------------

    public function getTitle()
    {
        return $this->title;
    }

    //-----------------------------------------------------------------------------

    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }

    //-----------------------------------------------------------------------------

    public function getCaption()
    {
        return $this->caption;
    }

    //-----------------------------------------------------------------------------

    public function __toString()
    {
        $attributes = array();
        if ($this->getId() !== null) {
            $attributes[] = sprintf('id="%s"', $this->getId());
        }
        if ($this->getClass() !== null) {
            $attributes[] = sprintf('class="%s"', $this->getClass());
        }
        if ($this->getTitle() !== null) {
            $attributes[] = sprintf('title="%s"', $this->getTitle());
        }

        return sprintf(
            '<span %s>%s</span>',
            implode(' ', $attributes),
            $this->getCaption()
        );
    }
}
