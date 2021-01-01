<div id="thread-<?php echo $this->thread->hashId ?>" class="thread jThread">
<a id="<?php echo $this->thread->hashId ?>"></a><?php
if ($this->displayAuthors && array_key_exists($this->thread->createdBy, $this->profiles)) {
?><a class="image" href="<?php echo $this->path ?>user/view/<?php echo $this->thread->createdBy ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$this->thread->createdBy],
'title' => $this->profiles[$this->thread->createdBy]->getHtmlLocation(),
'width' => 50,
'height' => 50));
?></a><?php
} else {
?><img alt="" class="image" width="50" height="50" src="/img/lemongras/default-thumb-o.png" /><?php
}
$class = 'threadArticle';
if ($this->currentUser !== null && $this->currentUser->id == $this->thread->createdBy) {
$class .= ' myThreadArticle';
}
if (array_key_exists(\vc\config\EntityTypes::FORUM_THREAD, $this->flags) &&
array_key_exists($this->thread->id, $this->flags[\vc\config\EntityTypes::FORUM_THREAD])) {
$class .= ' flaggedThreadArticle';
}
?>
<div class="<?php echo $class ?>"><?php
echo $this->element(
'forum/threadArticle',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'displayAuthors' => $this->displayAuthors,
'thread' => $this->thread,
'profiles' => $this->profiles,
'pictures' => $this->pictures,
'isLikable' => $this->isLikable,
'actions' => $this->actions,
'likes' => $this->likes,
'flags' => $this->flags
)
);
?></div>
<div class="clearfix"></div>
<div class="comments jComments"><?php
if (count($this->thread->comments) > \vc\config\Globals::DEFAULT_THREAD_COMMENT_COUNT) {
array_shift($this->thread->comments);
?><div class="loadMoreComments">
<a href="#" data-thread-id="<?php echo $this->thread->hashId ?>"><?php
echo gettext('forum.thread.loadMoreComments');
?></a>
</div><?php
}
foreach ($this->thread->comments as $threadComment) {
echo $this->element('forum/comment',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'displayAuthors' => $this->displayAuthors,
'threadHashId' => $this->thread->hashId,
'threadComment' => $threadComment,
'currentUser' => $this->currentUser,
'actions' => $this->commentActions[$threadComment->id],
'isLikable' => $this->isLikable,
'pictures' => $this->pictures,
'profiles' => $this->profiles,
'likes' => $this->likes,
'flags' => $this->flags));
}
?></div>
<?php if ($this->canPostComment) { ?>
<div class="newcomment">
<?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation(),
'width' => 35,
'height' => 35));
?>
<div class="formWrapper">
<div class="form">
<form action="#">
<input type="hidden" name="thread" value="<?php echo $this->thread->hashId ?>" />
<textarea name="body" class="jAutoHeight" rows="1"></textarea>
<div class="loader" style="display:none"></div>
<button class="save" title="<?php echo gettext('forum.comment.confirm')?>"><span><?php echo gettext('forum.comment.confirm')?></span></button>
</form>
</div>
</div>
</div>
<?php } ?>
</div>