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
?>
<div id="groupSettings"><?php
echo $this->renderForm($this->form);
?></div><?php
$this->addScript(
'vc.groups.settings.init();'
);
