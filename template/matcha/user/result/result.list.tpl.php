<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo $this->shortTitle ?></h1><?php
echo $this->element('actionbar',
array(
'actions' => $this->actions,
'customContent' => $this->element(
'userResultFilter',
array(
'path' => $this->path,
'requestQuery' => $this->requestQuery,
'filterSize' => $this->filterSize,
'pagination' => $this->pagination
)
)
));
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
echo $this->element(
'pagination',
array('pagination' => $this->pagination)
);
if ($this->notificationSearchstring != null) {
?><div class="notifyInfo"><?php echo $this->notificationSearchstring ?></div><?php
}
?><ul class="itembox"><?php
foreach ($this->profiles as $profile) {
echo $this->element(
'profilebox',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'currentUser'=>$this->currentUser,
'sessionSettings' => $this->sessionSettings,
'profile'=>$profile,
'picture'=>$this->pictures[$profile->id],
'matchPercentage'=>(
array_key_exists($profile->id, $this->matchPercentages)
? $this->matchPercentages[$profile->id]
: null
),
'isAdmin'=>$this->isAdmin,
'ownFavorites' => $this->ownFavorites,
'ownFriendsConfirmed' => $this->ownFriendsConfirmed,
'ownFriendsToConfirm' => $this->ownFriendsToConfirm,
'ownFriendsWaitForConfirm' => $this->ownFriendsWaitForConfirm,
'blocked' => $this->blocked,
'usersOnline' => $this->usersOnline
)
);
}
?></ul><?php
echo $this->element(
'pagination',
array('pagination' => $this->pagination)
);
$this->echoWideAd($this->locale, $this->plusLevel);
?><section class="formHighlight">
<h3><?php echo gettext('result.savesearch')?></h3>
<p><?php echo gettext('result.savesearch.infotext')?></p>
<form accept-charset="UTF-8" action="<?php echo $this->path ?>user/result/save/" class=" horizontal" method="post">
<input name="url" value="<?php echo $this->escapeAttribute(implodeQuery($this->requestQuery)) ?>" type="hidden" />
<ul>
<li>
<input id="save-name" type="text" name="name" maxlength="256" placeholder="<?php echo gettext("result.savesearch.name")?>"/>
</li>
<li>
<select id="save-interval" name="interval" size="1">
<option value="0"><?php echo gettext("result.savesearch.message.none")?></option>
<option value="1"><?php echo gettext("result.savesearch.message.daily")?></option>
<option value="7" selected="selected"><?php echo gettext("result.savesearch.message.weekly")?></option>
<option value="30"><?php echo gettext("result.savesearch.message.monthly")?></option>
</select>
</li>
<li>
<select name="type" size="1">
<option value="1"><?php echo gettext("result.savesearch.message.new_profiles")?></option>
<option value="2"><?php echo gettext("result.savesearch.message.updated_profiles")?></option>
</select>
</li>
<li>
<button type="submit"><?php echo gettext("result.savesearch.commit")?></button>
</li>
</ul>
</form>
</section><?php
$this->echoWideAd($this->locale, $this->plusLevel);
$this->addScript('vc.ui.profilesToOpen = [' . implode(',', $this->profileIds) . '];');
