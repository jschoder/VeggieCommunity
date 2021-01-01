<?php
namespace vc\design\matcha;

class DesignConfig extends \vc\design\AbstractDesignConfig
{
    public function getTemplateDirectories()
    {
        return array('matcha');
    }

    public function hasBlocks()
    {
        return true;
    }

    public function hasOldMailbox()
    {
        return false;
    }
}
