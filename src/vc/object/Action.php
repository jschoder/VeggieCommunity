<?php
namespace vc\object;

class Action extends ActionStub
{
    private $onclick = null;

    private $href = null;

    private $data = array();

    private $important = false;

    public function setOnclick($onclick)
    {
        $this->onclick = $onclick;
        return $this;
    }

    public function getOnclick()
    {
        return $this->onclick;
    }

    public function setHref($href)
    {
        $this->href = $href;
        return $this;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function setData($field, $value)
    {
        $this->data[$field] = $value;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setImportant($important = true)
    {
        $this->important = $important;
        return true;
    }

    public function isImportant()
    {
        return $this->important;
    }

    public function __toString()
    {
        $attributes = array();
        if ($this->getId() !== null) {
            $attributes[] = 'id="' . $this->getId() . '"';
        }
        if ($this->getClass() !== null) {
            $attributes[] = 'class="' . $this->getClass() . '"';
        }
        if ($this->getOnclick() !== null) {
            $attributes[] = 'onclick="' . $this->getOnclick() . '"';
        }
        foreach ($this->getData() as $field => $value) {
            $attributes[] = 'data-' . $field . '="' . $value . '"';
        }
        if ($this->getHref() !== null) {
            $attributes[] = 'href="' . $this->getHref() . '"';
        } else {
            $attributes[] = 'href="#"';
        }
        if ($this->getTitle() !== null) {
            $attributes[] = 'title="' . $this->getTitle() . '"';
        }

        if ($this->getOnclick() !== null || $this->getHref() !== null) {
            return sprintf(
                '<a %s>%s</a>',
                implode(' ', $attributes),
                $this->getCaption()
            );
        } else {
            return sprintf(
                '<span %s>%s</span>',
                implode(' ', $attributes),
                $this->getCaption()
            );
        }
    }
}
