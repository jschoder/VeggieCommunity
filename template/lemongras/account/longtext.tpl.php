<h1><?php echo $this->title?></h1>
<?php
$lines = explode("\n", prepareHTML($this->longtext, false));
foreach ($lines as $line)
{
if (strpos($line, 'h2. ') === 0)
{
echo('<h2>' . trim(substr($line, 3)) . '</h2>');
}
else
{
echo('<p>' . trim($line) . '</p>');
}
}