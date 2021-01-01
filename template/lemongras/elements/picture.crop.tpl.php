<?php
if ($this->picture instanceof \vc\object\SavedPicture)  {
echo '<img alt="" width="' . intval($this->width) . '" height="' . intval($this->height) . '" ';
if (empty($this->title)) {
$this->title = $this->picture->getDescription();
}
if (!empty($this->title)) {
echo 'title="' . prepareHTML($this->title, false) . '" ';
}
if (!empty($this->class)) {
echo 'class="' . $this->class . '" ';
}
echo 'src="/user/picture/crop/74/74/' . $this->picture->filename . '" />';
} elseif ($this->picture instanceof \vc\object\DefaultPicture) {
echo '<img alt="" width="' . intval($this->width) . '" height="' . intval($this->height) . '" ';
if (empty($this->title)) {
$this->title = gettext($this->picture->hiddenImage ? 'DefaultPicture.hidden' : 'DefaultPicture.default');
}
if (!empty($this->title)) {
echo 'title="' . prepareHTML($this->title, false) . '" ';
}
if (!empty($this->class)) {
echo 'class="' . $this->class . '" ';
}
switch ($this->picture->gender) {
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
}
echo 'src="' . $this->imagesPath . $filename . '" />';
} ?>