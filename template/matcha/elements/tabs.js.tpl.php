<nav class="jTabs <?php echo $this->class ?>"><ul><?php
foreach ($this->tabs as $tab) {
$href = $this->path . $tab['path'];
if (strpos($href, '?') === FALSE) {
$href .= '/';
}
echo '<li><a href="' . $href . '" data-tab="' . $tab['tabId'] . '"';
if ($tab['active']) {
echo ' class="active"';
}
echo '>' . $tab['caption'] . '</a></li>';
}
?></ul></nav>