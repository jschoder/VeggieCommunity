<?php
namespace vc\object;

class DefaultPicture extends Picture
{
    public $gender;
    public $hiddenImage;

    public function isDefaultPic()
    {
        return true;
    }
}
