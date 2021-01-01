<form class="modular" name="<?php echo $this->name ?>" action="<?php echo $this->path . $this->target ?>" method="<?php echo $this->method ?>" accept-charset="UTF-8"<?php if ($this->isMultipart) { echo ' enctype="multipart/form-data"'; } ?>>
<dl><?php
echo($this->content);
?></dl>
</form>