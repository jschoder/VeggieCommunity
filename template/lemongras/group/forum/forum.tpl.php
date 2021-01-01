<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->element('groups/header',
array('path' => $this->path,
'currentUser' => $this->currentUser,
'group' => $this->group,
'groupRoles' => $this->groupRoles,
'memberCount' => $this->memberCount,
'isConfirmedMember' => $this->isConfirmedMember,
'isMemberWaitingForConfirmation' => $this->isMemberWaitingForConfirmation,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed));
echo $this->element('tabs/group',
array('path' => $this->path,
'site' => $this->site,
'siteParams' => $this->siteParams,
'currentUser' => $this->currentUser,
'groupRole' => $this->groupRole,
'group' => $this->group,
'forums' => $this->forums));
?><div id="groupForum" class="jsForum">
<h2><?php echo prepareHtml($this->currentForum->name) ?></h2><?php
if ($this->isConfirmedMember) {
if (in_array($this->currentForum->id, $this->forumSubscriptions)) {
?><a class="subscribeForum" title="<?php echo gettext('group.forum.subscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>" style="display:none"></a><?php
?><a class="unsubscribeForum" title="<?php echo gettext('group.forum.unsubscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>"></a><?php
} else {
?><a class="subscribeForum" title="<?php echo gettext('group.forum.subscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>"></a><?php
?><a class="unsubscribeForum" title="<?php echo gettext('group.forum.unsubscribe') ?>" data-forum-id="<?php echo $this->currentForum->hashId ?>" style="display:none"></a><?php
}
}
if ((
$this->currentForum->contentVisibility == \vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER ||
$this->currentForum->contentVisibility == \vc\object\GroupForum::CONTENT_VISIBILITY_REGISTERED
) &&
$this->currentUser === null) {
?><div class="notifyInfo"><?php echo gettext('group.visibility.forum.registered') ?></div><?php
} elseif ($this->currentForum->contentVisibility == \vc\object\GroupForum::CONTENT_VISIBILITY_MEMBER &&
!$this->isConfirmedMember) {
?><div class="notifyInfo"><?php echo gettext('group.visibility.forum.member') ?></div><?php
} else {
if ($this->canPostThread) {
echo $this->element('forum/newThreadForm',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser' => $this->currentUser,
'ownPicture' => $this->ownPicture,
'contextType' => vc\config\EntityTypes::GROUP_FORUM,
'contextId' => $this->currentForum->hashId));
}
?><div class="jThreads threads"><?php
foreach ($this->threads as $thread) {
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
}
?></div><?php
}
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
'vc.groups.forum.init(' .
'\'' . $this->currentForum->hashId . '\',' .
$this->page . ',' .
($this->isConfirmedMember ? 'true' : 'false') . ',' .
(empty($this->lastUpdateTimestamp) ? '0' : $this->lastUpdateTimestamp) .
');'
);
