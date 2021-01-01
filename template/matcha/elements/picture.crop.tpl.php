<?php
if ($this->picture instanceof \vc\object\SavedPicture) :
echo '<img alt=""  ';
if (empty($this->title)):
$this->title = $this->picture->getDescription();
endif;
if (!empty($this->title)):
echo 'title="' . prepareHTML($this->title, false) . '" ';
endif;
if (!empty($this->class)):
echo 'class="' . $this->class . '" ';
endif;
if (isset($this->big) && $this->big):
$path = '200/200';
else:
$path = '74/74';
endif;
echo 'src="/user/picture/crop/' . $path . '/' . $this->picture->filename . '" />';
elseif ($this->picture instanceof \vc\object\DefaultPicture):
echo '<img alt="" ';
if (empty($this->title)):
$this->title = gettext($this->picture->hiddenImage ? 'DefaultPicture.hidden' : 'DefaultPicture.default');
endif;
if (!empty($this->title)):
echo 'title="' . prepareHTML($this->title, false) . '" ';
endif;
if (!empty($this->class)):
echo 'class="' . $this->class . '" ';
endif;
switch ($this->picture->gender):
case 2:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-thumb-m.png';
break;
case 4:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-thumb-f.png';
break;
case 6:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-thumb-o.png';
break;
default:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-thumb-a.png';
endswitch;
echo 'src="';
if (isset($this->big) && $this->big):
echo $this->imagesPath . $filename;
else:
echo $this->imagesPath . 'thumb/' . $filename;
endif;
echo '" />';
endif;
