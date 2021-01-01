<article id="thread-<?php echo $this->thread->hashId ?>" class="bubbleW jThread">
<a id="<?php echo $this->thread->hashId ?>"></a>
<aside><?php
if ($this->displayAuthors && array_key_exists($this->thread->createdBy, $this->profiles)):
?><a class="label" href="<?php echo $this->path ?>user/view/<?php echo $this->thread->createdBy ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->pictures[$this->thread->createdBy],
'title' => $this->profiles[$this->thread->createdBy]->getHtmlLocation()));
?></a><?php
else:
?><a class="label" href="<?php echo $this->path ?>account/signup/">
<img alt="" class="image" src="/img/matcha/thumb/default-thumb-o.png" />
</a><?php
endif;
?></aside><?php
$class = 'bubble threadArticle';
if ($this->currentUser !== null && $this->currentUser->id == $this->thread->createdBy):
$class .= ' my';
endif;
if (array_key_exists(\vc\config\EntityTypes::FORUM_THREAD, $this->flags) &&
array_key_exists($this->thread->id, $this->flags[\vc\config\EntityTypes::FORUM_THREAD])):
$class .= ' mod';
endif;
?><div class="<?php echo $class ?>"><?php
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
'flags' => $this->flags,
'canPostComment' => $this->canPostComment
)
);
?></div>
<section class="comments<?php if (empty($this->thread->comments)) { echo ' empty'; } ?>">
<div class="jComments"><?php
if (count($this->thread->comments) > \vc\config\Globals::DEFAULT_THREAD_COMMENT_COUNT):
array_shift($this->thread->comments);
?><div class="loadMoreComments">
<a href="#" data-thread-id="<?php echo $this->thread->hashId ?>"><?php
echo gettext('forum.thread.loadMoreComments');
?></a>
</div><?php
endif;
foreach ($this->thread->comments as $threadComment):
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
endforeach;
?></div><?php
if ($this->canPostComment):
?><aside>
<nav>
<a class="addComment jAddComment" href="#"> <span><?php echo gettext('forum.thread.addcomment.long') ?></span></a>
</nav>
</aside><?php
?><article class="bubbleW newcomment">
<aside>
<a class="label" href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation()));
?></a>
</aside>
<div class="bubble form">
<form action="#">
<input type="hidden" name="thread" value="<?php echo $this->thread->hashId ?>" />
<ul>
<li>
<textarea rows="1" class="jAutoHeight" name="body"></textarea>
</li>
</ul>
<div class="actions">
<div class="jLoading loader hidden"></div>
<button title="<?php echo gettext('forum.comment.confirm')?>" class="save"></button>
</div>
</form>
</div>
</article><?php
endif;
?></section>
</article>