<article class="bubbleW jThreadComment"  id="thread-comment-<?php echo $this->threadComment->hashId ?>" >
<a id="<?php echo $this->threadHashId ?>-<?php echo $this->threadComment->hashId ?>"></a>
<aside><?php
if ($this->displayAuthors && array_key_exists($this->threadComment->createdBy, $this->profiles)):
?><a class="label" href="<?php echo $this->path ?>user/view/<?php echo $this->threadComment->createdBy ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$this->threadComment->createdBy],
'title' => $this->profiles[$this->threadComment->createdBy]->getHtmlLocation()));
?></a><?php
else:
?><a class="label" href="<?php echo $this->path ?>account/signup/">
<img alt="" class="label" src="/img/matcha/thumb/default-thumb-o.png" />
</a><?php
endif
?></aside><?php
$class = 'bubble threadCommentArticle';
if ($this->currentUser !== null && $this->currentUser->id == $this->threadComment->createdBy):
$class .= ' my';
endif;
if (array_key_exists(\vc\config\EntityTypes::FORUM_COMMENT, $this->flags) &&
array_key_exists($this->threadComment->id, $this->flags[\vc\config\EntityTypes::FORUM_COMMENT])):
$class .= ' mod';
endif;
?><div class="<?php echo $class ?>">
<header><?php
if ($this->displayAuthors):
if (array_key_exists($this->threadComment->createdBy, $this->profiles)):
$profile = $this->profiles[$this->threadComment->createdBy];
$class = array();
if ($profile->realMarker):
$class[] = 'real';
endif;
if ($profile->plusMarker):
$class[] = 'plus';
endif;
?><a <?php if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; } ?>href="/de/user/view/<?php echo $this->threadComment->createdBy ?>/"><?php
echo prepareHTML($profile->nickname)
?></a> <?php
else:
?><span class="unknown"><?php echo gettext('forum.thread.deleteduser') ?> </span><?php
endif;
else:
?><span class="unknown"><?php echo gettext('forum.thread.unknownuser') ?> </span><?php
endif;
?><span class="jAgo" data-ts="<?php echo strtotime($this->threadComment->createdAt) ?>"></span>
<?php if (!empty($this->actions)): ?>
<aside class="popup">
<span class="context jTrigger" onclick="void(0)" tabindex="0"></span>
<nav class="menu"><?php
foreach ($this->actions as $action):
echo $action->__toString();
endforeach;
?></nav>
</aside>
<?php endif; ?>
</header>
<p><?php
echo prepareHTML($this->threadComment->body, true, true)
?></p>
<aside>
<nav><?php
if ($this->isLikable):
?><a class="jLike like" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></a><?php
?><div class="popup">
<span class="i jTrigger" tabindex="0" onclick="void(0)">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'];
endif;
?>)</span>
<ul class="menu left jReload" data-url="<?php echo $this->path ?>like/list/<?php echo \vc\config\EntityTypes::FORUM_COMMENT ?>/<?php echo $this->threadComment->hashId ?>/likes/"></ul>
</div><?php
?><a class="jDislike dislike" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></a><?php
?><div class="popup">
<span class="i jTrigger" tabindex="0" onclick="void(0)">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'];
endif;
?>)</span>
<ul class="menu left jReload" data-url="<?php echo $this->path ?>like/list/<?php echo \vc\config\EntityTypes::FORUM_COMMENT ?>/<?php echo $this->threadComment->hashId ?>/dislikes/"></ul>
</div><?php
else:
?><span class="like" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></span><?php
?><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['likes'];
endif;
?>)</span>
<span class="dislike" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->threadComment->hashId ?>"></span><?php
?><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_COMMENT][$this->threadComment->id]['dislikes'];
endif;
?>)</span><?php
endif;
?></nav>
</aside>
</div>
</article>