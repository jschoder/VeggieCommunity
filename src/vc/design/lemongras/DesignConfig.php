<?php
namespace vc\design\lemongras;

class DesignConfig extends \vc\design\AbstractDesignConfig
{
    public function getTemplateDirectories()
    {
        return array('lemongras');
    }

    public function hasBlocks()
    {
        return false;
    }

    public function hasOldMailbox()
    {
        return true;
    }
}
