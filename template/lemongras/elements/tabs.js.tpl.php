<div id="subnav" class="jTabs clearfix"><?php
foreach ($this->tabs as $tab) {
$href = $this->path . $tab['path'];
if (strpos($href, '?') === FALSE) {
$href .= '/';
}
echo '<a href="' . $href . '" data-tab="' . $tab['tabId'] . '"';
if ($tab['active']) {
echo ' class="active"';
}
echo '>' . $tab['caption'] . '</a>';
}
?></div>