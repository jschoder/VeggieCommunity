<?php
require(dirname(__FILE__) . '/../head.html5.tpl.php');
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
?><header>
<div class="bodyW">
<h1 id="logo"><a href="<?php echo $titleUrl; ?>" ><img alt="<?php echo gettext('header.title') . ' - ' . gettext('header.motto') ?>" src="/img/matcha/logo.png"  title="<?php echo gettext('header.title') . ' - ' . gettext('header.motto') ?>" /></a></h1>
<a class="jMenu" href="#"><span><?php echo gettext('menu.popup') ?></span></a>
<nav id="notify"><?php
if ($this->currentUser !== null):
?><div class="popup">
<span class="pm jTrigger" tabindex="0" onclick="void(0)" title="<?php echo gettext('menu.messages')?>"></span>
<ul class="menu jReload" data-url="<?php echo $this->path ?>notifications/pm/"></ul>
<span class="count jNewMessages"<?php if ($this->newMessages == 0) { echo ' style="display:none"'; } ?>><span><?php echo $this->newMessages ?></span></span>
</div><?php
endif;
if (!empty($this->ownFriendsConfirmed) || !empty($this->ownFriendsToConfirm)):
?><div class="popup">
<span class="friends jTrigger" tabindex="0" onclick="void(0)" title="<?php echo gettext('menu.friendinbox')?>"></span>
<ul class="menu jReload" data-url="<?php echo $this->path ?>notifications/friends/"></ul>
<span class="count jOpenFriendRequests"<?php if ($this->openFriendRequests == 0) { echo ' style="display:none"'; } ?>><span><?php echo $this->openFriendRequests ?></span></span>
</div><?php
endif;
if (!empty($this->ownGroups)):
?><div class="popup">
<span class="groups jTrigger" tabindex="0" onclick="void(0)" title="<?php echo gettext('menu.groups')?>"></span>
<ul class="menu jReload" data-url="<?php echo $this->path ?>notifications/groups/"></ul>
<span class="count jGroupNotifications"<?php if ($this->groupNotifications == 0) { echo ' style="display:none"'; } ?>><span><?php echo $this->groupNotifications ?></span></span>
</div><?php
endif;
/*
<div class="popup">
<span class="events jTrigger" tabindex="0" onclick="void(0)" title="<?php echo gettext('menu.events')?>"></span>
<ul class="menu jReload" data-url="<?php echo $this->path ?>notifications/events/"></ul>
</div>
*/ ?>
<?php if ($this->isAdmin):
if ($this->modOpenTickets > 0 || $this->modSpam > 0 || $this->modSubmittedReals > 0 || $this->modUnconfirmedGroups > 0):
$display = '';
$modNumber = $this->modOpenTickets . '|' . ($this->modSpam + $this->modSubmittedReals + $this->modUnconfirmedGroups);
else:
$display = ' style="display:none"';
$modNumber = '';
endif;
?><a class="mod" href="<?php echo $this->path; ?>mod/" >
<span class="count jMods"<?php echo $display ?>><span><?php echo $modNumber ?></span></span>
</a><?php
endif;
if ($this->currentUser !== null):
?><span class="separator"></span>
<a class="plus" href="<?php echo $this->path; ?>plus/" title="<?php echo gettext('menu.plus')?>"></a><?php
endif;
?><div><?php
if (empty($this->ticketNotifications)):
$helpPath = 'help/faq/';
else:
$helpPath = 'help/support/';
endif;
?><a class="faq" href="<?php echo $this->path . $helpPath; ?>" title="<?php echo gettext('menu.help')?>" data-active="<?php echo $this->path; ?>help/support/" data-inactive="<?php echo $this->path; ?>help/faq/"></a>
<span class="count jTicketNotifications"<?php if (empty($this->ticketNotifications)) { echo ' style="display:none"'; } ?>><span><?php echo $this->ticketNotifications ?></span></span>
</div><?php
if ($this->currentUser !== null):
?><a class="logout" href="<?php echo $this->path; ?>logout/" title="<?php echo gettext("user.logout")?>"></a><?php
endif;
?></nav>
</div>
</header>
<div class="bodyW">
<div id="sidebars"><?php
if (!isset($this->hideMenu) || $this->hideMenu === false):
?><section id="userNav">
<?php if ($this->currentUser === null): ?>
<?php if ($this->site !== 'login'): ?>
<section class="formHighlight">
<h3><?php echo gettext('box.login.title') ?></h3>
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>login/" class="login" method="post">
<input type="hidden" name="loginTargetUrl" value="<?php echo $this->loginTargetUrl; ?>" />
<ul>
<li class="mandatory">
<input maxlength="255" name="login-email" placeholder="<?php echo gettext('box.login.username') ?>" type="text" />
</li>
<li class="mandatory">
<input maxlength="100" name="login-password" placeholder="<?php echo gettext('box.login.password') ?>" type="password" />
</li>
<li>
<input class="check" id="login-auto" name="login-autologin" value="true" type="checkbox"/>
<label for="login-auto"><?php echo gettext("box.login.autologin")?></label>
</li>
<li>
<button class="login" type="submit"><?php echo gettext('box.login.confirm') ?></button>
</li>
</ul>
</form>
<?php /*
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" method="post">
<ul>
<li>
<button class="fb" type="submit"><?php echo gettext('box.login.loginFb') ?></button>
</li>
</ul>
</form>
*/ ?>
<aside>
<a href="<?php echo $this->path; ?>account/rememberpassword/"><?php echo gettext('box.login.lostpassword') ?></a>
</aside>
</section>
<section class="formHighlight">
<h3><?php echo gettext('box.registration.title') ?></h3>
<p><?php echo gettext('box.registration.notAccountYet') ?></p>
<?php /*
<form accept-charset="UTF-8" action="<?php echo $this->path; ?>fb/login/" method="post">
<ul>
<li><a class="button cta signup" href="<?php echo $this->path ?>account/signup/"><?php echo gettext('box.registration.signUpNow') ?></a></li>
<li><button class="fb" type="submit"><?php echo gettext('box.registration.signUpFb') ?></button></li>
</ul>
</form>
*/ ?>
</section>
<?php endif; ?>
<nav>
<ul>
<li><a class="users" href="<?php echo $this->path ?>user/search/"><?php echo gettext('menu.users') ?></a></li>
<li><a class="groups" href="<?php echo $this->path ?>groups/search/"><?php echo gettext('menu.groups') ?></a></li>
<li><a class="events" href="<?php echo $this->path ?>events/calendar/"><?php echo gettext('menu.events') ?></a></li>
<li><a class="chat" href="<?php echo $this->path ?>chat/"><?php echo gettext('menu.chat') ?></a></li>
<li><a class="faq" href="<?php echo $this->path ?>help/faq/"><?php echo gettext('help.tab.faq') ?></a></li>
<li><a class="help" href="<?php echo $this->path ?>help/support/"><?php echo gettext('help.tab.feedback') ?></a></li>
</ul>
</nav>
<?php else: ?>
<section class="welcome">
<a href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture));
echo $this->currentUser->nickname;
?></a>
<p><?php echo $this->greeting ?></p><?php
if ($this->isAdmin):
?><div>
<form action="<?php echo $this->path ?>user/result/" method="get" accept-charset="UTF-8">
<input type="text" name="userid" maxlength="250" value="" />
</form>
</div><?php
endif;
?></section>
<nav>
<ul>
<li>
<span class="collapsible" data-area="menugroup-mysite" title="<?php echo gettext('menu.collapse'); ?>"></span>
<a class="mysite" href="<?php echo $this->path ?>mysite/"><?php echo gettext('menu.mysite') ?></a>
</li>
<li id="menugroup-mysite">
<ul>
<li><a class="pm" href="<?php echo $this->path ?>pm/"><?php echo gettext('menu.messages') ?></a></li>
</ul>
</li>
<li><?php
if (!empty($this->ownFriendsConfirmed) || !empty($this->ownFriendsToConfirm) || !empty($this->ownFavorites)):
?><span class="collapsible" data-area="menugroup-users" title="<?php echo gettext('menu.collapse'); ?>"></span><?php
endif;
?><a class="users" href="<?php echo $this->path ?>user/search/"><?php echo gettext('menu.users') ?></a>
</li>
<li id="menugroup-users">
<ul><?php
if (!empty($this->ownFriendsConfirmed) || !empty($this->ownFriendsToConfirm)):
?><li><a class="friends" href="<?php echo $this->path ?>friend/list/"><?php echo gettext('mysite.tab.friends') ?></a></li><?php
endif;
if (!empty($this->ownFavorites)):
?><li><a class="favorites" href="<?php echo $this->path ?>favorite/list/"><?php echo gettext('mysite.tab.favorites') ?></a></li><?php
endif;
?></ul>
</li>
<li><?php
if (empty($this->ownGroups)):
$groupsUrl = $this->path . 'groups/search/';
else:
$groupsUrl = $this->path . 'groups/';
?><span class="collapsible" data-area="menugroup-groups" title="<?php echo gettext('menu.collapse'); ?>"></span><?php
endif;
?><a class="groups" href="<?php echo $groupsUrl ?>"><?php echo gettext('menu.groups') ?></a>
</li>
<?php if (!empty($this->ownGroups)) : ?>
<li id="menugroup-groups">
<ul><?php
foreach ($this->ownGroups as $groupHashId => $groupName):
$title = prepareHTML($groupName);
?><li><a class="groups" href="<?php echo $this->path .'groups/info/' . $groupHashId . '/' ?>" title="<?php echo $title ?>"><?php echo $title ?></a></li><?php
endforeach;
?></ul>
</li>
<?php endif; ?>
<li><?php
if (empty($this->ownEvents)):
$eventsUrl = $this->path . 'events/calendar/';
else:
$eventsUrl = $this->path . 'events/';
?><span class="collapsible" data-area="menugroup-events" title="<?php echo gettext('menu.collapse'); ?>"></span><?php
endif;
?><a class="events" href="<?php echo $eventsUrl ?>"><?php echo gettext('menu.events') ?></a>
</li>
<?php if (!empty($this->ownEvents)) : ?>
<li id="menugroup-events">
<ul><?php
foreach ($this->ownEvents as $eventHashId => $event):
$title = $this->getShortDate($event[0]) . ' ' . prepareHTML($event[1]);
?><li><a class="events" href="<?php echo $this->path .'events/view/' . $eventHashId . '/' ?>" title="<?php echo $title ?>"><?php echo $title ?></a></li><?php
endforeach;
?></ul>
</li>
<?php endif; ?>
<li><a class="chat" href="<?php echo $this->path ?>chat/"><?php echo gettext('menu.chat') ?></a></li>
<li>
<span class="collapsible" data-area="menugroup-profile" title="<?php echo gettext('menu.collapse'); ?>"></span>
<a class="profile" href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/"><?php echo gettext("face.ownprofile.view")?></a>
</li>
<li id="menugroup-profile">
<ul>
<li><a class="edit" href="<?php echo $this->path ?>user/edit/"><?php echo gettext("face.ownprofile.edit")?></a></li>
<li><a class="pictures" href="<?php echo $this->path ?>user/pictures/"><?php echo gettext('face.ownpics.edit')?></a></li>
<li><a class="matching" href="<?php echo $this->path ?>user/matching/"><?php echo gettext("face.ownprofile.editmatching")?></a></li>
<li><a class="real" href="<?php echo $this->path ?>account/real/"><?php echo gettext('menu.real')?></a></li>
<li><a class="plus" href="<?php echo $this->path ?>plus/"><?php echo gettext('menu.plus')?></a></li>
<li><a class="settings" href="<?php echo $this->path ?>account/settings/"><?php echo gettext('face.changesettings')?></a></li>
<li><a class="blocked" href="<?php echo $this->path ?>account/blocked/"><?php echo gettext('blocked.title')?></a></li>
<li><a class="password" href="<?php echo $this->path ?>account/changepassword/"><?php echo gettext("mysite.settings.actions.editpassword")?></a></li>
<li><a class="delete" href="<?php echo $this->path ?>account/delete/"><?php echo gettext("mysite.settings.actions.unregister")?></a></li>
</ul>
</li><?php
/*
<li><a class="chat" href="<?php echo $this->path ?>chat/"><?php echo gettext('menu.chat') ?></a></li>
<li>
<a class="settings" href="<?php echo $this->path ?>account/actions/"><?php echo gettext("menu.actions")?></a>
</li><?php
*/
if ($this->isAdmin):
?><li>
<span class="collapsible" data-area="menugroup-mod" title="<?php echo gettext('menu.collapse'); ?>"></span>
<a class="dashboard" href="<?php echo $this->path ?>mod/">Mod Dashboard</a>
</li>
<li id="menugroup-mod">
<ul>
<li><a class="chart" href="<?php echo $this->path ?>mod/server/">Server</a></li>
<li><a class="chart" href="<?php echo $this->path ?>mod/metrics/">Metrics</a></li>
<li><a class="help" href="<?php echo $this->path ?>mod/tickets/">Tickets <span class="jModTickets">(<span><?php echo $this->modOpenTickets ?></span>)</span></a></li>
<li><a class="flag" href="<?php echo $this->path ?>mod/spam/">Spam <span class="jModSpam">(<span><?php echo $this->modSpam ?></span>)</span></a></li>
<li><a class="flag" href="<?php echo $this->path ?>mod/flag/">Flags <span class="jModFlag">(<span><?php echo $this->modFlag ?></span>)</span></a></li>
<li><a class="suspicion" href="<?php echo $this->path ?>mod/suspicions/">Suspicions</a></li>
<li><a class="delete" href="<?php echo $this->path ?>mod/user/deletereasons/50/">Delete Reasons</a></li>
<li><a class="real" href="<?php echo $this->path ?>mod/real/">Real Check <span class="jModReals">(<span><?php echo $this->modSubmittedReals ?></span>)</span></a></li>
<li><a class="groups" href="<?php echo $this->path ?>mod/groups/">Unfonfirmed groups <span class="jModGroups">(<span><?php echo $this->modUnconfirmedGroups ?></span>)</span></a></li>
<li><a class="pm" href="<?php echo $this->path ?>mod/unsent/">Unsent messages <span class="jModPms">(<span><?php echo $this->modUnsentMessages ?></span>)</span></a></li>
<li><a class="pictures" href="<?php echo $this->path ?>mod/pictures/unchecked/">Unchecked Pictures <span class="jModPicsUnchecked">(<span><?php echo $this->modPicsUnchecked ?></span>)</span></a></li>
<li><a class="pictures" href="<?php echo $this->path ?>mod/pictures/prewarned/">Prewarned Pictures <span class="jModPicsPrewarned">(<span><?php echo $this->modPicsPrewarned ?></span>)</span></a></li>
<li><a class="net" href="<?php echo $this->path ?>mod/duplicates/">Duplicate IPs</a></li>
<li><a class="pm" href="<?php echo $this->path ?>mod/toldafriend/">Told A Friend <span class="jModToldAFriend">(<span><?php echo $this->modToldafriend ?></span>)</span></a></li>
<li><a class="bullhorn" href="<?php echo $this->path ?>mod/messenger/">Messenger</a></li>
</ul>
</li><?php
endif;
?><li><a class="faq" href="<?php echo $this->path ?>help/faq/"><?php echo gettext('help.tab.faq') ?></a></li>
<li><a class="help" href="<?php echo $this->path ?>help/support/"><?php echo gettext('help.tab.feedback') ?></a></li>
</ul>
</nav>
<?php endif; ?>
</section><?php
endif;
if (!empty($this->blocks)):
?><section id="blocks"><?php
echo $this->blocks;
?></section><?php
endif;
if (isset($this->customSidebar)):
print $this->customSidebar;
endif;
?></div>
<main<?php if (empty($this->blocks)) { echo ' class="wide"'; } ?>><?php
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
?></main>
</div>
<footer>
<div class="bodyW">
<div class="lang">
<a href="/de/<?php echo $this->noLocalePath ?>">Deutsch</a>
<a href="/en/<?php echo $this->noLocalePath ?>">English</a>
</div>
<div>
<a href="<?php echo $this->path ?>tellafriend/"><?php echo gettext('menu.tellafriend') ?></a>
<?php if ($this->currentUser !== null): ?>
<a href="<?php echo $this->path ?>account/termsofservice/"><?php echo gettext('menu.termsofuse') ?></a>
<a href="<?php echo $this->path ?>account/privacypolicy/"><?php echo gettext('menu.privacypolicy') ?></a>
<?php endif; ?>
<a href="<?php echo $this->path ?>about/"><?php echo gettext('menu.about') ?></a>
</div>
<div>
<span class="copy">&copy; 2001-<?php echo vc\config\Globals::COPYRIGHT_YEAR ?> Joachim Schoder. <?php echo gettext('face.allrightsreserved')?>.</span>
</div>
</div>
</footer>
<?php require(dirname(__FILE__) . '/../foot.html5.tpl.php');
