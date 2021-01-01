<header><?php
if(!empty($this->thread->subject)):
?><h3><?php echo prepareHTML($this->thread->subject, true, true) ?></h3><?php
endif;
if ($this->displayAuthors):
if (array_key_exists($this->thread->createdBy, $this->profiles)) :
$profile = $this->profiles[$this->thread->createdBy];
$class = array();
if ($profile->realMarker):
$class[] = 'real';
endif;
if ($profile->plusMarker):
$class[] = 'plus';
endif;
?><a <?php if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; } ?>href="/de/user/view/<?php echo $this->thread->createdBy ?>/"><?php
echo prepareHTML($profile->nickname)
?></a> <?php
else:
?><span class="unknown"><?php echo gettext('forum.thread.deleteduser') ?></span> <?php
endif;
else:
?><span class="unknown"><?php echo gettext('forum.thread.unknownuser') ?></span> <?php
endif;
?><span class="jAgo" data-ts="<?php echo strtotime($this->thread->createdAt) ?>"></span>
<?php if (!empty($this->actions)): ?>
<aside class="popup">
<span class="context jTrigger" onclick="void(0)" tabindex="0"></span>
<nav class="menu"><?php
foreach ($this->actions as $action) {
echo $action->__toString();
}
?></nav>
</aside>
<?php endif; ?>
</header><?php
$additional = $this->thread->additional;
if ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_PROFILE_UPDATE):
print '<p>' . str_replace(
'%NICKNAME%',
prepareHTML($profile->nickname),
gettext('activity.profileupdated')
) . '</p>';
elseif ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_PICTURE_ADDED):
print '<p>' . str_replace(
'%NICKNAME%',
prepareHTML($profile->nickname),
gettext('activity.uploadedpic')
) . '</p>';
elseif ($this->thread->threadType == \vc\object\ForumThread::TYPE_ACTVITY_FRIEND_ADDED):
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
endif;
if ($this->thread->has(\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME)):
$picturePath = $additional[\vc\object\ForumThread::ADDITIONAL_PICTURE_PATH];
$pictureFile = $additional[\vc\object\ForumThread::ADDITIONAL_PICTURE_FILENAME];
?><div class="picture">
<a class="jZoom" href="<?php echo '/' . $picturePath . '/full/' . $pictureFile ?>">
<img alt="" src="<?php echo '/' . $picturePath . '/w/390/' . $pictureFile ?>" />
</a>
</div><?php
endif;
if ($this->thread->has(\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_URL) &&
$this->thread->has(\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_TITLE)):
$previewUrl = $additional[\vc\object\ForumThread::ADDITIONAL_LINK_PREVIEW_URL];
if (strpos($previewUrl, 'https://') !== 0 && strpos($previewUrl, 'http://') !== 0 && strpos($previewUrl, '/') !== 0):
$previewUrl = $this->path . $previewUrl;
endif;
?><a class="linkbox" href="<?php echo $previewUrl ?>"><?php
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
if (!empty($this->thread->body)):
?><p><?php echo prepareHTML($this->thread->body, true, true) ?></p><?php
endif;
?><aside>
<nav><?php
if ($this->isLikable):
?><a class="jLike like" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></a><?php
?><div class="popup">
<span class="i jTrigger" tabindex="0" onclick="void(0)">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'];
endif;
?>)</span>
<ul class="menu left jReload" data-url="<?php echo $this->path ?>like/list/<?php echo \vc\config\EntityTypes::FORUM_THREAD ?>/<?php echo $this->thread->hashId ?>/likes/"></ul>
</div><?php
?><a class="jDislike dislike" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></a><?php
?><div class="popup">
<span class="i jTrigger" tabindex="0" onclick="void(0)">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'];
endif;
?>)</span>
<ul class="menu left jReload" data-url="<?php echo $this->path ?>like/list/<?php echo \vc\config\EntityTypes::FORUM_THREAD ?>/<?php echo $this->thread->hashId ?>/dislikes/"></ul>
</div><?php
else:
?><span class="like" href="#" title="<?php echo gettext('like.liking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></span><?php
?><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['likes'];
endif;
?>)</span>
<span class="dislike" href="#" title="<?php echo gettext('like.disliking') ?>" data-entity-id="<?php echo $this->thread->hashId ?>"></span><?php
?><span class="i">(<?php
if (empty($this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'])):
echo '0';
else:
echo $this->likes[\vc\config\EntityTypes::FORUM_THREAD][$this->thread->id]['dislikes'];
endif;
?>)</span><?php
endif;
if ($this->canPostComment):
?><a class="addComment jAddComment" href="#"> <span><?php echo gettext('forum.thread.addcomment') ?></span></a><?php
endif;
?></nav>
</aside>