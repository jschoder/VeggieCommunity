<?php require(dirname(__FILE__) . '/../head.tpl.php');
if ($this->currentUser === null) {
$titleUrl = $this->path;
} else {
$titleUrl = $this->path . 'mysite/';
}
if (isset($this->websocketKey)) {
$this->addScript(
'vc.websocket.key = \'' . $this->websocketKey . '\';'
);
}
?><div id="headerWrapper">
<div id="header" class="clearfix">
<div id="logo">
<a href="<?php echo $titleUrl ?>">
<img class="link" src="<?php echo $this->imagesPath; ?>logo.png" width="169" height="60" title="<?php echo gettext("title")?>" alt="<?php echo gettext("title")?>" />
</a>
</div><!-- #logo -->
<ul id="notify" class="clearfix">
<li><a class="icons pm" href="<?php echo $this->path; ?>pm/" title="<?php echo gettext('menu.messages')?>">
<span class="count jNewMessages" <?php if ($this->newMessages == 0) { echo 'style="display:none"'; } ?>><span><?php echo $this->newMessages ?></span></span>
</a></li>
<li><a class="icons friends" href="<?php echo $this->path; ?>friend/list/" title="<?php echo gettext('menu.friendinbox')?>">
<span class="count jOpenFriendRequests" <?php if ($this->openFriendRequests == 0) { echo 'style="display:none"'; } ?>><span><?php echo $this->openFriendRequests ?></span></span>
</a></li>
<li class="submenu popup jHoverTrigger">
<a class="icons groups" href="<?php echo $this->path; ?>groups/" title="<?php echo gettext('menu.groups')?>" tabindex="0" onclick="void(0)"></a>
<span class="count jGroupNotifications" <?php if ($this->groupNotifications == 0) { echo 'style="display:none"'; } ?>><span><?php echo $this->groupNotifications ?></span></span>
<div class="menu">
<ul class="groupNotifications jReload" data-url="<?php echo $this->path ?>notifications/groups"></ul>
</div>
</li>
<li class="language group">
<a class="de<?php if ($this->locale == 'de') { echo(' active'); } ?>" href="/de/">Deutsch</a>
<a class="en<?php if ($this->locale == 'en') { echo(' active'); } ?>" href="/en/">English</a>
</li>
<li class="group"><a class="icons plus" href="<?php echo $this->path; ?>plus/" title="<?php echo gettext('menu.plus')?>">
</a></li>
<?php if ($this->currentUser !== null) { ?>
<li class="submenu">
<a class="icons profile" href="#"></a>
<?php if ($this->isAdmin) {
if ($this->modOpenTickets > 0 || $this->modSpam > 0 || $this->modUnconfirmedGroups > 0) {
$display = '';
$modNumber = $this->modOpenTickets . ',' . $this->modSpam . ',' . $this->modUnconfirmedGroups;
} else {
$display = ' style="display:none"';
$modNumber = '';
}
?>
<span class="count jMods"<?php echo $display ?>><span><?php echo $modNumber ?></span></span>
<?php } ?>
<div class="menu">
<ul class="profile">
<li><a href="<?php echo $this->path; ?>user/view/<?php echo $this->currentUser->id; ?>/" class="view"><?php echo gettext("face.ownprofile.view")?></a></li>
<li><a href="<?php echo $this->path; ?>user/edit/" class="edit"><?php echo gettext("face.ownprofile.edit")?></a></li>
<li><a href="<?php echo $this->path; ?>account/delete/" class="edit"><?php echo gettext("mysite.settings.actions.unregister")?></a></li>
<li><a href="<?php echo $this->path; ?>account/changepassword/" class="edit"><?php echo gettext("mysite.settings.actions.editpassword")?></a></li>
<li><a href="<?php echo $this->path; ?>account/settings/" class="settings"><?php echo gettext('face.changesettings')?></a></li>
<li><hr /></li>
<li><a href="<?php echo $this->path; ?>logout/" class="logout"><?php echo gettext("user.logout")?></a></li>
</ul>
</div>
</li>
<?php } ?>
<li><a class="icons help" href="<?php echo $this->path; ?>help/faq/" title="<?php echo gettext('menu.help')?>"></a></li>
</ul>
<?php
/*
if ($this->site !== 'start'):
$this->echoWideAd($this->locale, $this->plusLevel);
endif;
*/
?></div><!-- #header -->
</div><!-- #headerWrapper -->
<div id="navWrapper">
<div id="navigation" class="clearfix">
<ul class="clearfix"><?php
$items = array();
$items[] = array('menu.mysite', 'mysite/', 'mysite');
$items[] = array('menu.users', 'user/search/', 'users');
if ($this->currentUser === null) {
$items[] = array('menu.events', 'events/calendar/', 'events');
$items[] = array('menu.groups', 'groups/search/', 'groups');
} else {
$items[] = array('menu.events', 'events/', 'events');
$items[] = array('menu.groups', 'groups/', 'groups');
}
$items[] = array('menu.chat', 'chat/', 'chat');
$items[] = array('menu.help', 'help/faq/', 'help');
foreach ($items as $item)
{
$url = $this->path . $item[1];
$caption = gettext($item[0]);
if (!empty($this->menuitem) && $this->menuitem == $item[2]) {
?><li class="active"><a href="<?php echo $url?>"><?php echo $caption?></a></li><?php
} else {
?><li><a href="<?php echo $url?>"><?php echo $caption?></a></li><?php
}
}
?></ul>
</div><!-- #navigation -->
</div><!-- #navWrapper -->
<div id="wrapper">
<div id="contentWrapper" class="clearfix"><?php
if (!empty($this->wideContent) && $this->wideContent) { ?>
<div id="wideContent">
<div id ="content"><?php echo $this->content; ?></div>
</div>
<?php } else { ?>
<div id="siteContent">
<div id ="content"><?php
/*
?><div class="notifyWarn"><?php
if ($this->locale == 'de'):
?>Heute gegen 10:00 Uhr wird VeggieCommunity.org ein Update erhalten. Es kann in der Zeit zu Ausfällen oder Fehlern kommen.<?php
else:
?>Today around 10:00 a.m. (CET) VeggieCommunity.org will receive an update. This might lead to downtime or errors.<?php
endif;
?></div><?php
*/
echo $this->content;
?></div>
</div><!-- #siteContent -->
<div id="sidebar">
<?php if ($this->currentUser !== null) { ?>
<ul id="usermenu">
<li class="ProfileLinks clearfix">
<h2><?php echo gettext("user.hello")?> <?php echo $this->currentUser->nickname; ?></h2>
<div class="image">
<?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'width' => 100,
'height' => 100));
?>
<a href="<?php echo $this->path; ?>user/pictures/" class="edit"><?php echo gettext('face.ownpics.edit.short')?></a>
</div>
<div class="links">
<div class="profileActions">
<a href="<?php echo $this->path; ?>user/view/<?php echo $this->currentUser->id; ?>/" class="view"><?php echo gettext("face.ownprofile.view")?></a>
<a href="<?php echo $this->path; ?>user/edit/" class="edit"><?php echo gettext("face.ownprofile.edit")?></a>
<a href="<?php echo $this->path; ?>user/matching/" class="edit"><?php echo gettext("face.ownprofile.editmatching")?></a>
<a href="<?php echo $this->path; ?>account/settings/" class="settings"><?php echo gettext('face.changesettings')?></a>
</div>
<a href="<?php echo $this->path; ?>logout/" class="logout"><?php echo gettext("user.logout")?></a>
</div>
</li>
<li><a class="normal" href="<?php echo $this->path; ?>pm/"><?php echo gettext('menu.messages')?>
<span class="jNewMessages" <?php if ($this->newMessages == 0) { echo 'style="display:none"'; } ?>>(<span><?php echo $this->newMessages ?></span>)</span></a>
</li>
<li><a class="normal" href="<?php echo $this->path; ?>friend/list/"><?php echo gettext('menu.friendinbox')?>
<span class="jOpenFriendRequests" <?php if ($this->openFriendRequests == 0) { echo 'style="display:none"'; } ?>>(<span><?php echo $this->openFriendRequests ?></span>)</span></a></li><?php
if ($this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_LAST_VISITOR)) {
?><li id="users">
<a href="#" id="sideblock-lastvisitors-header" class="hiddenslide" onclick="switchSidebarBlock('sideblock-lastvisitors','user/list/lastvisitors/9');return false;"><?php echo gettext("start.popup.lastvisitors")?></a>
<div id="sideblock-lastvisitors"></div>
</li><?php
}
?></ul><!-- #usermenu -->
<?php } else { ?>
<div id="sideLogin">
<h2><?php echo gettext("box.login.title")?></h2>
<form action="<?php echo $this->path; ?>login/" method="post" accept-charset="UTF-8">
<input type="hidden" name="loginTargetUrl" value="<?php echo $this->loginTargetUrl; ?>" />
<ul class="clearfix">
<li>
<label for="login-email"><?php echo gettext("box.login.username")?> </label>
<input id="login-email" class="text130" type="text" name="login-email" maxlength="255" />
</li>
<li>
<label for="login-password"><?php echo gettext("box.login.password")?> </label>
<input id="login-password" class="text130" type="password" name="login-password" maxlength="100" />
</li>
<li class="clearfix">
<p class="autoLogin">
<input class="check" id="login-auto" name="login-autologin" value="true" type="checkbox"/>
<label for="login-auto"><?php echo gettext("box.login.autologin")?> </label>
</p>
<button type="submit"><?php echo gettext("box.login.confirm")?></button>
</li>
</ul>
</form>
<?php
/*
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" class="fb" method="post">
<ul>
<li>
<button class="fb" type="submit"><?php echo gettext('box.login.loginFb') ?></button>
</li>
</ul>
</form>
*/ ?>
<a class="lostPW" href="<?php echo $this->path; ?>account/rememberpassword/"><?php echo gettext("box.login.lostpassword")?></a>
<p class="signUp"><?php echo gettext('face.nomember.yet')?><a href="<?php echo $this->path; ?>account/signup/"><?php echo gettext('face.signuphere')?></a></p>
</div>
<?php }
if (isset($this->polls)) {
foreach ($this->polls as $poll) { ?>
<div class="poll">
<p><?php echo gettext('face.poll.title')?></p>
<h3><?php echo $poll->question?></h3>
<div id="poll<?php echo $poll->id?>"><?php
if ($poll->own_vote == 0) {
echo $this->element('poll.form',
array('path' => $this->path,
'poll' => $poll));
} else {
echo $this->element('poll.result',
array('path' => $this->path,
'poll' => $poll));
}
$pollMessage = str_replace('%DATE%',
$this->prepareDate(gettext('face.poll.validtill.dateformat'), $poll->end_time),
gettext('face.poll.validtill'));
?></div><?php
?><p class="infoText"><?php echo $pollMessage?></p>
</div><!-- .poll -->
<?php }
} ?>
<ul id="users">
<li>
<a href="#" id="sideblock-onlineprofiles-header" class="hiddenslide" onclick="switchSidebarBlock('sideblock-onlineprofiles','user/list/onlineprofiles/30');return false;"><?php echo gettext("start.popup.online")?></a>
<div id="sideblock-onlineprofiles"></div>
</li>
<li>
<a href="#" id="sideblock-newprofiles-header" class="hiddenslide" onclick="switchSidebarBlock('sideblock-newprofiles','user/list/newprofiles/15');return false;"><?php echo gettext("start.popup.newprofiles")?></a>
<div id="sideblock-newprofiles"></div>
</li>
<li>
<a href="#" id="sideblock-lastupdates-header" class="hiddenslide" onclick="switchSidebarBlock('sideblock-lastupdates','user/list/lastupdates/15');return false;"><?php echo gettext("start.popup.lastupdates")?></a>
<div id="sideblock-lastupdates"></div>
</li>
</ul><!-- #users -->
</div><!-- #sidebar -->
<?php } ?>
</div><!-- #contentWrapper -->
</div><!-- #wrapper -->
<div id="footerWrapper">
<div id="footer">
<div id="footerLinks" class="clearfix">
<ul class="clearfix">
<?php
$footerMenu = array();
$link = new \stdClass();
$link->href = $this->path . 'tellafriend/';
$link->caption = gettext('menu.tellafriend');
$footerMenu[] = $link;
if ($this->currentUser !== null) {
$link = new \stdClass();
$link->href = $this->path . 'account/termsofservice/';
$link->caption = gettext('menu.termsofuse');
$footerMenu[] = $link;
$link = new \stdClass();
$link->href = $this->path . 'account/privacypolicy/';
$link->caption = gettext('menu.privacypolicy');
$footerMenu[] = $link;
}
$link = new \stdClass();
$link->href = $this->path . 'about/';
$link->caption = gettext('menu.about');
$footerMenu[] = $link;
foreach ($footerMenu as $link) {
echo sprintf(
'<li><a class="menulink" href="%s">%s</a></li>',
$link->href,
$link->caption
);
}
?>
</ul>
<a href="#" class="top"><?php echo gettext('face.backtotop')?></a>
</div>
<div id="legal" class="clearfix">
<ul class="clearfix">
<li>&copy; 2001-<?php echo vc\config\Globals::COPYRIGHT_YEAR ?> Joachim Schoder. <?php echo gettext('face.allrightsreserved')?>.</li>
</ul>
</div>
</div><!-- #footer -->
</div><!-- #footerWrapper -->
<?php require(dirname(__FILE__) . '/../foot.tpl.php'); ?>