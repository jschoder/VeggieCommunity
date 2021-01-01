<li class="image"><?php
if (empty($this->default)):
?><div class="ajaxUpload ajaxUploadPreview" data-path="<?php echo $this->form->getPath() ?>picture/upload/" title="<?php echo $this->caption ?>"></div>
<input class="ajaxUploadSelect" type="file" data-max-size="<?php echo $this->maxSize ?>" accept="image/jpeg,image/gif,image/png" />
<input class="ajaxUploadFile" type="hidden" name="<?php echo $this->name ?>" /><?php
else:
?><div class="ajaxUploadPreview">
<img alt="" src="<?php echo '/' . $this->rootPath . '/crop/74/74/' . $this->default ?>" />
</div><?php
endif;
?></li>