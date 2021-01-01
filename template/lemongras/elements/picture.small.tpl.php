<?php
if ($this->picture instanceof \vc\object\SavedPicture) {
echo '<img alt="" width="' . intval($this->picture->smallwidth) . '" height="' . intval($this->picture->smallheight) . '" ';
if (empty($this->title)) {
$this->title = $this->picture->getDescription();
}
if (!empty($this->title)) {
echo 'title="' . prepareHTML($this->title, false) . '" ';
}
if (!empty($this->class)) {
echo 'class="' . $this->class . '" ';
}
echo 'src="/user/picture/small/' . $this->picture->filename . '" />';
} elseif ($this->picture instanceof \vc\object\DefaultPicture) {
echo '<img alt="" width="125" height="154"';
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
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-small-m.png';
break;
case 4:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-small-f.png';
break;
case 6:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-small-o.png';
break;
default:
$filename = ($this->picture->hiddenImage ? 'hidden': 'default') . '-small-a.png';
}
echo 'src="' . $this->imagesPath . $filename . '" />';
} ?>