<article class="bubbleW" id="newthread">
<aside>
<a href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/" class="label"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation()));
?></a>
</aside>
<div class="bubble form">
<form action="#">
<input type="hidden" name="context_type" value="<?php echo $this->contextType ?>" />
<input type="hidden" name="context_id" value="<?php echo $this->contextId ?>" />
<ul>
<li>
<input type="text" placeholder="<?php echo gettext('forum.thread.subject') ?>" maxlength="255" name="subject">
</li>
<li>
<textarea placeholder="<?php echo gettext('forum.thread.body') ?>" rows="2" class="jAutoHeight" name="body"></textarea>
</li>
<li>
<div class="ajaxUpload ajaxUploadPreview" data-path="<?php echo $this->path ?>picture/upload/" data-filename="true" data-delete="true"></div>
<input class="ajaxUploadSelect" type="file" data-max-size="<?php echo \vc\config\Globals::MAX_UPLOAD_FILE_SIZE ?>" accept="image/jpeg,image/gif,image/png" />
<input class="ajaxUploadFile" type="hidden" name="picture" />
</li>
</ul>
<div class="actions">
<div class="jLoading loader hidden"></div>
<button title="<?php echo gettext('forum.thread.confirm')?>" class="save"></button>
<button title="<?php echo gettext('forum.thread.addPicture') ?>" class="secondary picture jFileuploadTrigger"></button>
</div>
</form>
</div>
</article>