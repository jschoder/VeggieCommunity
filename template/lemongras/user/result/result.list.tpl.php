<h1><?php echo $this->shortTitle; ?></h1>
<div class="resultOptions">
<p>&nbsp;</p>
<p class="links"><?php
foreach ($this->actions as $action)
{
if ($action->getClass() !== 'savesearch')
{
echo($action->__toString());
}
}
?></p>
</div><?php
echo $this->element('tabs/user.search',
array('path' => $this->path,
'site' => $this->site,
'requestQuery' => $this->requestQuery));
echoNavigation($this);
if ($this->notification != null) {
echo $this->element('notification',
array('notification' => $this->notification));
}
if ($this->notificationSearchstring != null) {
?><div class="notifyInfo"><?php echo $this->notificationSearchstring ?></div><?php
}
?>
<ul id="userList" class="clearfix"><?php
foreach ($this->profiles as $profile) {
echo $this->element(
'profilebox',
array(
'path' => $this->path,
'imagesPath' => $this->imagesPath,
'usersOnline'=>$this->usersOnline,
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
'blocked' => $this->blocked
)
);
}
?></ul><?php
echoNavigation($this);
?><div id="savesearchList">
<div class="savesearch">
<h3><?php echo gettext('result.savesearch')?></h3>
<p class="text"><?php echo gettext('result.savesearch.infotext')?></p>
<form action="<?php echo $this->path ?>user/result/save/" method="post" accept-charset="UTF-8">
<p class="name">
<input name="url" value="<?php echo $this->escapeAttribute(implodeQuery($this->requestQuery)) ?>" type="hidden" />
<label for="save-name"><?php echo gettext("result.savesearch.name")?> </label>
<input id="save-name" class="text130" type="text" name="name" maxlength="256" />
</p>
<p class="mail">
<select id="save-interval" name="interval" size="1">
<option value="0"><?php echo gettext("result.savesearch.message.none")?></option>
<option value="1"><?php echo gettext("result.savesearch.message.daily")?></option>
<option value="7" selected="selected"><?php echo gettext("result.savesearch.message.weekly")?></option>
<option value="30"><?php echo gettext("result.savesearch.message.monthly")?></option>
</select>
<select name="type" size="1">
<option value="1"><?php echo gettext("result.savesearch.message.new_profiles")?></option>
<option value="2"><?php echo gettext("result.savesearch.message.updated_profiles")?></option>
</select>
<button type="submit"><?php echo gettext("result.savesearch.commit")?></button>
</p>
</form>
</div>
</div><?php
$this->addScript('vc.ui.profilesToOpen = [' . implode(',', $this->profileIds) . '];');
//-----------------------------------------------------------------------------
function echoNavigation($view)
{
?><div id="pager" class="clearfix">
<div class="pages">
<form action="<?php echo $view->path ?>user/result/" method="get" accept-charset="UTF-8"><?php
echoLimitationOptions($view);
echo ' ' . gettext('result.totalSizeOf') . ' ' . $view->pagination->getTotalCount();
?></form>
</div><?php
echo $view->element(
'pagination',
array(
'imagesPath' => $view->imagesPath,
'pagination' => $view->pagination
)
);
?></div><?php
}
//-----------------------------------------------------------------------------
function echoLimitationOptions($view)
{
foreach ($view->requestQuery as $key=>$value)
{
if ($key != "limit")
{
if (is_array($value))
{
foreach ($value as $arrayValue)
{
?><input name="<?php echo $key?>[]" value="<?php echo prepareFormValue($arrayValue)?>" type="hidden" /><?php
}
}
else
{
?><input name="<?php echo $key?>" value="<?php echo prepareFormValue($value)?>" type="hidden" /><?php
}
}
}
?><select class="text" name="limit" size="1" onchange="submit()" title="<?php echo gettext('result.limitation.title') ?>"><?php
foreach (array(12, 24, 36, 48, 60, 90, 120, 150, 180, 250, 300, 450) as $value)
{
echo("<option");
if ($value == $view->filterSize)
{
echo(" selected=\"selected\"");
}
echo(">" . $value . "</option>");
}
?></select><?php
}