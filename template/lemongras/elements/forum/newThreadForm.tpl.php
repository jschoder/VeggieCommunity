<div id="newthread" class="clearfix">
<?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation(),
'width' => 50,
'height' => 50));
?>
<div class="formWrapper">
<div class="form">
<form action="#">
<input type="hidden" name="context_type" value="<?php echo $this->contextType ?>" />
<input type="hidden" name="context_id" value="<?php echo $this->contextId ?>" />
<input type="text" name="subject" maxlength="255" title="<?php echo gettext('forum.thread.subject') ?>" />
<textarea name="body" class="jAutoHeight" title="<?php echo gettext('forum.thread.body') ?>" rows="3"></textarea>
<div>
<div class="ajaxUpload ajaxUploadPreview" data-path="<?php echo $this->path ?>picture/upload/" data-filename="true" data-delete="true"></div>
<input class="ajaxUploadSelect" type="file" data-max-size="<?php echo \vc\config\Globals::MAX_UPLOAD_FILE_SIZE ?>" accept="image/jpeg,image/gif,image/png" />
<input class="ajaxUploadFile" type="hidden" name="picture" />
</div>
<div class="loader" style="display:none"></div>
<button class="save" type="submit" title="<?php echo gettext('forum.thread.confirm')?>">
<span><?php echo gettext('forum.thread.confirm')?></span>
</button>
<button title="<?php echo gettext('forum.thread.addPicture') ?>" class="secondary picture jFileuploadTrigger"><span></span></button>
</form>
</div>
</div>
</div><!-- #newthread -->