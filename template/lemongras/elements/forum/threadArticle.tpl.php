<?php if(empty($this->thread->subject)) {
?><div class="user pictureUser"><?php
} else {
?><h3><?php echo prepareHTML($this->thread->subject, true, true) ?></h3>
<div class="user"><?php
}
if ($this->displayAuthors) {
if (array_key_exists($this->thread->createdBy, $this->profiles)) {
$profile = $this->profiles[$this->thread->createdBy];
?><a <?php if($profile->plusMarker) { echo 'class="plus" '; } ?>href="/de/user/view/<?php echo $this->thread->createdBy ?>/"><?php
echo prepareHTML($profile->nickname)
?></a> <?php
} else {
?><span class="unknown"><?php echo gettext('forum.thread.deleteduser') ?> </span><?php
}
} else {
?><span class="unknown"><?php echo gettext('forum.thread.unknownuser') ?> </span><?php
}
?><span class="date">(<span class="jAgo" data-ts="<?php echo strtotime($this->thread->createdAt) ?>"></span>)</span>
</div><?php
$additional = $this->thread->additional;
if ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_PROFILE_UPDATE):
?><div class="body"><p><?php
print str_replace(
'%NICKNAME%',
prepareHTML($profile->nickname),
gettext('activity.profileupdated')
);
?></p></div><?php
elseif ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_PICTURE_ADDED):
?><div class="body"><p><?php
print str_replace(
'%NICKNAME%',
prepareHTML($profile->nickname),
gettext('activity.uploadedpic')
);
?></p></div><?php
elseif ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_FRIEND_ADDED):
?><div class="body"><?php
$newFriendId = $additional[\vc\object\ForumThread::ADDITIONAL_ACTIVITY_PROFILE_ID];
if (array_key_exists($newFriendId, $this->profiles) && !empty($profile)):
$friend = $this->profiles[$newFriendId];
print '<p>' . str_replace(
array(
'%NICKNAME%',
'%FRIEND%',
),
array(
prepareHTML($profile->nickname),
'<a href="' . $this->path . 'user/view/' . $friend->id . '/">' . prepareHTML($friend->nickname) . '</a>',
),
gettext('activity.addedfriend')
) . '</p>';
if (array_key_exists($newFriendId, $this->pictures) &&
$this->pictures[$newFriendId] instanceof vc\object\SavedPicture):
$pictureFile = $this->pictures[$newFriendId]->filename;
?><a class="jZoom" href="<?php echo '/user/picture/full/' . $pictureFile ?>">
<img alt="" src="<?php echo '/user/picture/w/390/' . $pictureFile ?>" />
</a><?php
endif;
else:
/* :TODO: JOE - error handling */
endif;
?></div><?php
endif;
if ($this->thread->has(\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME)) {
$picturePath = $additional[\vc\object\ForumThread::ADDITIONAL_PICTURE_PATH];
$pictureFile = $additional[\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME];
?><div class="picture">
<a class="jZoom" href="<?php echo '/' . $picturePath . '/full/' . $pictureFile ?>">
<img alt="" src="<?php echo '/' . $picturePath . '/w/390/' . $pictureFile ?>" />
</a>
</div><?php
if (!empty($this->thread->body)) {
?><div class="body"><?php echo prepareHTML($this->thread->body, true, true) ?></div><?php
}
} else {
?><div class="body"><?php echo prepareHTML($this->thread->body, true, true) ?></div><?php
}
if ($this->thread->has(\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_URL) &&
$this->thread->has(\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_TITLE)):
$previewUrl = $additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_URL];
if (strpos($previewUrl, 'https://') !== 0 && strpos($previewUrl, 'http://') !== 0 && strpos($previewUrl, '/') !== 0):
$previewUrl = $this->path . $previewUrl;
endif;
?><a class="linkbox clearfix" href="<?php echo $previewUrl ?>"><?php
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_PICTURE])):
?><img src="<?php echo $additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_PICTURE] ?>" /><?php
endif;
?><p class="title"><?php echo prepareHTML($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_TITLE]); ?></p><?php
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DESCRIPTION])):
?><p><?php echo prepareHTML($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DESCRIPTION]) ?></p><?php
endif;
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DATE]) ||
!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_LOCATION])):
?><p><?php
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DATE])):
echo formatLongDate($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DATE]);
endif;
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_DATE]) &&
!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_LOCATION])):
echo ', ';
endif;
if (!empty($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_LOCATION])):
echo prepareHTML($additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_LOCATION]);
endif;
?></p><?php
endif;
?></a><?php
endif;
?><div class="actions"><?php
if ($this->isLikable) {
?><a class="jLike fa fa-thumbs-o-up" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></a><span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'];
endif;
?>)</span></span>
<a class="jDislike fa fa-thumbs-o-down" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></a><span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'];
endif;
?>)</span></span><?php
} else {
?><span class="fa fa-thumbs-o-up" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'];
endif;
?>)</span>
<span class="fa fa-thumbs-o-down" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></span><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'];
endif;
?>)</span><?php
}
?></div>
<?php if (!empty($this->actions)) { ?>
<div class="context">
<a class="show-context">...</a>
<div class="context-menu jThreadContextMenu">
<ul><?php
foreach ($this->actions as $action) {
echo '<li>' . $action->__toString() . '</li>';
}
?></ul>
</div>
</div>
<?php } ?>