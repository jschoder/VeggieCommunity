<?php
if (!$this->isInline):
echo $this->element('tabs/mysite',
array('path' => $this->path,
'site' => $this->site,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFavorites' => $this->ownFavorites));
endif;
?><div id="profileForum" class="jsForum"><?php
if ($this->canPostThread && $this->pagination->getCurrentIndex() === 0) {
echo $this->element('forum/newThreadForm',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'contextType' => vc\config\EntityTypes::PROFILE,
'contextId' => $this->forumUserId));
}
?><div class="jThreads threads"><?php
foreach ($this->threads as $thread):
echo $this->element('forum/thread',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'displayAuthors' => $this->displayAuthors,
'thread' => $thread,
'currentUser' => $this->currentUser,
'isLikable' => $this->isLikable,
'canPostComment' => $this->canPostComment,
'ownPicture' => $this->ownPicture,
'pictures' => $this->pictures,
'profiles' => $this->profiles,
'actions' => $this->threadActions[$thread->id],
'commentActions' => $this->commentActions,
'likes' => $this->likes,
'flags' => $this->flags));
endforeach;
?></div><?php
echo $this->element(
'pagination',
array(
'imagesPath' => $this->imagesPath,
'pagination' => $this->pagination
)
);
?></div><?php
echo $this->element('forumTemplates');
$this->addScript(
'vc.forum.init(' .
\vc\config\EntityTypes::PROFILE . ',' .
$this->currentUser->id . ',' .
$this->page . ',' .
($this->currentUser === null ? 'false' : 'true') . ',' .
(empty($this->lastUpdateTimestamp) ? '0' : $this->lastUpdateTimestamp) .
');'
);
if ($this->isInline):
$script = $this->getScript();
?><script><?php
echo $script
?></script><?php
endif;
