<form name="<?php echo $this->name ?>" action="<?php echo $this->path . $this->target ?>" method="<?php echo $this->method ?>" accept-charset="UTF-8"<?php if ($this->isMultipart) { echo ' enctype="multipart/form-data"'; } ?>>
<ul><?php
echo $this->content;
?></ul>
</form>