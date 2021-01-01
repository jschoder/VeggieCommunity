<?php
namespace vc\design;

abstract class AbstractDesignConfig
{
    abstract public function getTemplateDirectories();

    abstract public function hasBlocks();

    abstract public function hasOldMailbox();
}
