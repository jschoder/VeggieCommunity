<?php
namespace vc\object\param;

abstract class AbstractPaginationObject
{
    private $urlPattern = '';

    private $defaultUrl = null;

    private $defaultUrlParams = null;

    abstract public function getCurrent();

    /**
     * Return false for non-existent button and null for empty button.
     */
    abstract public function getFirst();

    /**
     * Return false for non-existent button and null for empty button.
     */
    abstract public function getPrev();

    /**
     * Return false for non-existent button and null for empty button.
     */
    abstract public function getNext();

    /**
     * Return false for non-existent button and null for empty button.
     */
    abstract public function getLast();

    /**
     * Return an associative array with the url in the index and the caption in the value.
     */
    abstract public function getItems();

    public function setDefaultUrl($defaultUrl)
    {
        $this->defaultUrl = $defaultUrl;
    }

    public function setDefaultUrlParams($defaultUrlParams)
    {
        $this->defaultUrlParams = $defaultUrlParams;
    }

    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    protected function getUrl($params)
    {
        if ($this->defaultUrlParams !== null && $this->defaultUrlParams == $params) {
            return $this->defaultUrl;
        } else {
            return strtr($this->urlPattern, $params);
        }
    }
}
