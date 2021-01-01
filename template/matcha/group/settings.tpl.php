<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
echo $this->element('groups/header',
array('path' => $this->path,
'currentUser' => $this->currentUser,
'group' => $this->group,
'currentForum' => null,
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
?><div id="groupSettings"><?php
echo $this->renderForm($this->form);
?></div><?php
$this->echoWideAd($this->locale, $this->plusLevel);
$this->addScript(
'vc.groups.settings.init();'
);
