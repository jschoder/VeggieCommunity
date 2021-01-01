<div id="thread-comment-<?php echo $this->threadComment->hashId ?>" class="thread-comment jThreadComment">
<a id="<?php echo $this->threadHashId ?>-<?php echo $this->threadComment->hashId ?>"></a><?php
if ($this->displayAuthors && array_key_exists($this->threadComment->createdBy, $this->profiles)) {
?><a class="image" href="<?php echo $this->path ?>user/view/<?php echo $this->threadComment->createdBy ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$this->threadComment->createdBy],
'title' => $this->profiles[$this->threadComment->createdBy]->getHtmlLocation(),
'width' => 35,
'height' => 35));
?></a><?php
} else {
?><img alt="" class="image" width="35" height="35" src="/img/lemongras/default-thumb-o.png"><?php
}
$class = 'threadCommentArticle';
if ($this->currentUser !== null && $this->currentUser->id == $this->threadComment->createdBy) {
$class .= ' myThreadCommentArticle';
}
if (array_key_exists(\vc\config\EntityTypes::FORUM_COMMENT, $this->flags) &&
array_key_exists($this->threadComment->id, $this->flags[\vc\config\EntityTypes::FORUM_COMMENT])) {
$class .= ' flaggedThreadCommentArticle';
}
?>
<div class="<?php echo $class ?>">
<div class="user"><?php
if ($this->displayAuthors) {
if (array_key_exists($this->threadComment->createdBy, $this->profiles)) {
$profile = $this->profiles[$this->threadComment->createdBy];
?><a <?php if($profile->plusMarker) { echo 'class="plus" '; } ?>href="/de/user/view/<?php echo $this->threadComment->createdBy ?>/"><?php
echo prepareHTML($profile->nickname)
?></a> <?php
} else {
?><span class="unknown"><?php echo gettext('forum.thread.deleteduser') ?> </span><?php
}
} else {
?><span class="unknown"><?php echo gettext('forum.thread.unknownuser') ?> </span><?php
}
?><span class="date">(<span class="jAgo" data-ts="<?php echo strtotime($this->threadComment->createdAt) ?>"></span>)</span>
</div>
<div class="body"><?php
echo prepareHTML($this->threadComment->body, true, true)
?></div>
<div class="actions"><?php
if ($this->isLikable) {
?><a class="jLike fa fa-thumbs-o-up" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></a><span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'];
endif;
?>)</span></span>
<a class="jDislike fa fa-thumbs-o-down" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></a><span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'];
endif;
?>)</span></span><?php
} else {
?><span class="fa fa-thumbs-o-up" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'];
endif;
?>)</span>
<span class="fa fa-thumbs-o-down" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'];
endif;
?>)</span><?php
}
?></div>
</div>
<div class="clearfix"></div>
<?php if (!empty($this->actions)) { ?>
<div class="context">
<a class="show-context">...</a>
<div class="context-menu jThreadCommentContextMenu">
<ul><?php
foreach ($this->actions as $action) {
echo '<li>' . $action->__toString() . '</li>';
}
?></ul>
</div>
</div>
<?php } ?>
</div>