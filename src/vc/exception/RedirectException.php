<?php
namespace vc\exception;

class RedirectException extends \Exception
{
    private $isPermanently;
    
    public function __construct($message, $isPermanently = false)
    {
        parent::__construct($message, 0, null);
        $this->isPermanently = $isPermanently;
    }
    
    public function isPermanently()
    {
        return $this->isPermanently;
    }
}
