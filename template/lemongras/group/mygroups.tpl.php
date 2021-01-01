<?php
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
echo $this->element('tabs/groups',
array('path' => $this->path,
'site' => $this->site,));
?><div id="mygroups">
<div class="lastUpdates"><?php
if(empty($this->lastUpdates)) {
?><div class="notifyInfo"><?php echo gettext('groups.mygroups.empty') ?></div><?php
} else {
foreach ($this->lastUpdates as $groupHashId => $group) {
?><h2>
<a href="<?php echo $this->path ?>groups/info/<?php echo $groupHashId ?>/"><?php
if (empty($group['image'])) {
?><img src="/img/lemongras/default-group.png" width="30" height="30" /><?php
} else {
?><img src="/groups/picture/crop/74/74/<?php echo $group['image'] ?>" width="30" height="30" /><?php
}
?></a>
<a href="<?php echo $this->path ?>groups/info/<?php echo $groupHashId ?>/"><?php echo prepareHTML($group['name']) ?></a>
</h2>
<ul><?php
foreach ($group['forums'] as $forumHashId => $forum) {
?><li><?php
if ($forum['isMain']) {
$url = $this->path . 'groups/forum/' . $groupHashId . '/';
} else {
$url = $this->path . 'groups/forum/' . $groupHashId . '/' . $forumHashId . '/';
}
?><a href="<?php echo $url ?>"><?php echo prepareHTML($forum['name']) ?></a><br /><?php
if ($forum['lastComment'] === null &&
$forum['lastThread'] === null) {
echo gettext('groups.my.noposting');
} else if ($forum['lastComment'] === null ||
$forum['lastThread'] > $forum['lastComment']) {
echo sprintf(
gettext('groups.my.lastThread'),
prepareHTML($forum['lastThreadAuthorNickname']),
'<span class="jAgo" data-ts="' . $forum['lastThread'] . '"></span>'
);
} else {
echo sprintf(
gettext('groups.my.lastComment'),
prepareHTML($forum['lastCommentAuthorNickname']),
'<span class="jAgo" data-ts="' . $forum['lastComment'] . '"></span>'
);
}
?></li><?php
}
?></ul><?php
}
}
?></div>
<div class="secondary"><?php
if (!empty($this->unconfirmedMembers)) {
?><div class="unconfirmedMembers">
<h2><?php echo gettext('groups.unconfirmedMembers') ?></h2>
<ul><?php
foreach($this->unconfirmedMembers as $unconfirmedMember) {
?><li>
<a href="<?php echo $this->path ?>groups/info/<?php echo $unconfirmedMember['groupHashId'] ?>/#members"><?php
echo prepareHtml($unconfirmedMember['groupName']);
?></a> &gt;
<a <?php if($unconfirmedMember['profilePlusMarker']) { echo 'class="plus" '; } ?>href="<?php echo $this->path ?>user/view/<?php echo $unconfirmedMember['profileId'] ?>/"><?php
echo prepareHtml($unconfirmedMember['profileNickname']);
?></a>
(<span class="jAgo" data-ts="<?php echo $unconfirmedMember['groupMemberCreatedAt'] ?>"></span>)
</li><?php
}
?></ul>
</div><?php
}
if (!empty($this->flags)) {
?><div class="flags">
<h2><?php echo gettext('groups.flags') ?></h2>
<ul><?php
foreach($this->flags as $flag) {
?><li>
<a href="<?php echo $this->path ?>groups/info/<?php echo $flag['group_hash_id'] ?>/"><?php
echo prepareHtml($flag['group_name']);
?></a> >
<a href="<?php echo $this->path ?>groups/forum/<?php echo $flag['group_hash_id'] ?>/<?php echo $flag['forum_hash_id'] ?>/"><?php
echo prepareHtml($flag['forum_name']);
?></a><?php
if ($flag['entity_type'] == \vc\config\EntityTypes::FORUM_THREAD) {
$link = $this->path . 'groups/forum/' . $flag['group_hash_id'] . '/' . $flag['forum_hash_id'] .
'/#' . $flag['thread_id'];
?> > <a href="<?php echo $link ?>"><?php
echo gettext('groups.flags.thread');
?></a><?php
} else if ($flag['entity_type'] == \vc\config\EntityTypes::FORUM_COMMENT) {
$link = $this->path . 'groups/forum/' . $flag['group_hash_id'] . '/' . $flag['forum_hash_id'] .
'/#' . $flag['thread_id'] . '.' . $flag['comment_id'];
?> > <a href="<?php echo $link ?>"><?php
echo gettext('groups.flags.comment');
?></a><?php
}
?><br /><?php
$userLink = sprintf(
'<a %shref="%suser/view/%d/">%s</a>',
$flag['flagger_plus_marker'] ? 'class="plus" ' : '',
$this->path,
intval($flag['flagger_id']),
prepareHTML($flag['flagger_nickname'])
);
echo sprintf(
gettext('groups.flags.reportedOn'),
$userLink,
date(gettext('groups.flags.timestamp'), $flag['flagged_at'])
);
?></li><?php
}
?></ul>
</div><?php
}
if (!empty($this->invitations)) {
?><div class="invitations">
<h2><?php echo gettext('groups.invitations') ?></h2>
<ul><?php
foreach ($this->invitations as $invitation) {
?><li><?php
$class = $invitation['profilePlusMarker'] ? 'class="plus" ' : '';
echo sprintf(
gettext('groups.invitations.message'),
'<a ' . $class . 'href="' . $this->path . 'user/view/' . $invitation['profileId'] . '/">' .
prepareHTML($invitation['profileNickname']) .
'</a>',
'<a href="' . $this->path . 'groups/info/' . $invitation['groupHashId'] . '/">' .
prepareHTML($invitation['groupName']) .
'</a>'
);
if (!empty($invitation['comment'])) {
?><p><?php
echo prepareHTML($invitation['comment']);
?></p><?php
}
?><div class="date">
(<span class="jAgo" data-ts="<?php echo strtotime($invitation['createdAt']) ?>"></span>)
</div>
</li><?php
}
?></ul>
</div><?php
}
?></div>
</div>
