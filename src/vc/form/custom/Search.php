<?php
namespace vc\form\custom;

class Search extends \vc\form\MultipleChoice
{
    protected function getRenderTemplate()
    {
        return 'custom/search';
    }
}
