<nav class="<?php echo $this->class ?>"><ul><?php
$site = $this->site;
if (isset($this->siteParams)) {
$subSite = $site;
foreach ($this->siteParams as $param) {
$subSite .= '/' . $param;
foreach ($this->tabs as $tab) {
if ($subSite === $tab['path']) {
$site = $subSite;
}
}
}
} else {
$identicalPath = null;
$pathFit = null;
foreach ($this->tabs as $tab) {
if ($site === $tab['path']) {
$identicalPath = $tab['path'];
}
if (strpos($site, $tab['path']) === 0) {
$pathFit = $tab['path'];
}
}
if ($identicalPath === null &&
$pathFit !== null) {
$site = $pathFit;
}
}
foreach ($this->tabs as $tab) {
if (array_key_exists('href', $tab)) {
$href = $tab['href'];
} else {
$href = $this->path . $tab['path'];
}
if (strpos($href, '?') === FALSE) {
$href .= '/';
}
echo '<li><a href="' . $href . '"';
if ($site === $tab['path']) {
echo ' class="active"';
}
if (!empty($tab['title'])) {
echo ' title="' . $tab['title'] . '"';
}
echo '>';
if (!empty($tab['iconClass'])) {
echo '<span class="' . $tab['iconClass'] . '">';
}
echo prepareHTML($tab['caption'], false);
if (!empty($tab['iconClass'])) {
echo '</span>';
}
echo '</a></li>';
}
?></ul></nav>