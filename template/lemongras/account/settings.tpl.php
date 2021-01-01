<h1><?php echo gettext('mysite.settings.title') ?></h1><?php
echo $this->element('tabs/account',
array('path' => $this->path,
'site' => $this->site));
if ($this->notification) {
echo $this->element('notification',
array('notification' => $this->notification));
}
?><form action="<?php echo $this->path ?>account/settings/" method="post" accept-charset="UTF-8">
<div class="settings">
<h3><?php echo gettext("mysite.settings.actions")?></h3>
<a href="<?php echo $this->path ?>account/changepassword/"><?php echo gettext('mysite.settings.actions.editpassword')?></a><br />
<a href="<?php echo $this->path; ?>account/blocked/"><?php echo gettext('blocked.title')?></a><br />
<a href="<?php echo $this->path; ?>account/real/"><?php echo gettext('menu.real')?></a><br />
<a href="<?php echo $this->path ?>account/delete/"><?php echo gettext('mysite.settings.actions.unregister')?></a><br />
</div>
<div class="settings design">
<h3><?php echo gettext("mysite.settings.design")?></h3><?php
?><ul id="designs" class="clearfix"><?php
foreach ($this->designs as $design)
{
?><li><?php
?><input class="radio" id="design.<?php echo $design?>" name="design" <?php
if ($this->design == $design)
{
?>checked="checked" <?php
}
?>value="<?php echo $design?>" type="radio"/>
<label for="design.<?php echo $design?>"><?php echo gettext("mysite.settings.design." . $design)?></label><br />
<label for="design.<?php echo $design?>"><?php
?><img src="/img/<?php echo $design?>/design.png"
width="250" height="140" alt="" /><?php
?></label><?php
?></li><?php
}
?></ul>
</div>
<div class="settings mysite">
<h3><?php echo gettext("mysite.settings.mysite")?></h3>
<label for="savedSearchDisplay"><?php echo gettext("mysite.settings.savedSearch.display")?></label>
<select class="text130" id="savedSearchDisplay" name="savedSearchDisplay" size="1"><?php
$savedSearchDisplay = $this->sessionSettings->getValue(\vc\object\Settings::SAVEDSEARCH_DISPLAY);
?><option value="<?php echo \vc\object\Settings::SAVEDSEARCH_DISPLAY_INFOBOX?>" <?php
if ($savedSearchDisplay == \vc\object\Settings::SAVEDSEARCH_DISPLAY_INFOBOX)
{
?> selected="selected"<?php
}
?>><?php echo gettext("mysite.settings.savedSearch.display.infobox")?></option><?php
?><option value="<?php echo \vc\object\Settings::SAVEDSEARCH_DISPLAY_PICTURE?>" <?php
if ($savedSearchDisplay == \vc\object\Settings::SAVEDSEARCH_DISPLAY_PICTURE)
{
?> selected="selected"<?php
}
?>><?php echo gettext("mysite.settings.savedSearch.display.picture")?></option><?php
?><option value="<?php echo \vc\object\Settings::SAVEDSEARCH_DISPLAY_TEXT?>" <?php
if ($savedSearchDisplay == \vc\object\Settings::SAVEDSEARCH_DISPLAY_TEXT)
{
?> selected="selected"<?php
}
?>><?php echo gettext("mysite.settings.savedSearch.display.text")?></option><?php
?></select><br />
<label for="savedSearchCount"><?php echo gettext("mysite.settings.savedSearch.count")?></label>
<select class="text130" id="savedSearchCount" name="savedSearchCount" size="1"><?php
$savedSearchCount = $this->sessionSettings->getValue(\vc\object\Settings::SAVEDSEARCH_COUNT);
?><option value="6" <?php
if ($savedSearchCount == 6)
{
?> selected="selected"<?php
}
?>>6</option><?php
?><option value="12" <?php
if ($savedSearchCount == 12)
{
?> selected="selected"<?php
}
?>>12</option><?php
?><option value="24" <?php
if ($savedSearchCount == 24)
{
?> selected="selected"<?php
}
?>>24</option><?php
?><option value="36" <?php
if ($savedSearchCount == 36)
{
?> selected="selected"<?php
}
?>>36</option><?php
?></select><br />
</div>
<div class="settings notifications">
<h3><?php echo gettext("mysite.settings.pm")?></h3>
<input class="checkbox" id="ageRangeFilter" name="ageRangeFilter" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::AGE_RANGE_FILTER)) { ?> checked="checked" <?php } ?>/>
<label for="ageRangeFilter"><?php echo gettext("mysite.settings.ageRangeFilter")?></label><br />
<input class="checkbox" id="pmFilterIncoming" name="pmFilterIncoming" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::PM_FILTER_INCOMING)) { ?> checked="checked" <?php } ?>/>
<label for="pmFilterIncoming"><?php echo gettext("mysite.settings.pmFilterIncoming")?></label><br />
<input class="checkbox" id="pmFilterOutgoing" name="pmFilterOutgoing" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::PM_FILTER_OUTGOING)) { ?> checked="checked" <?php } ?>/>
<label for="pmFilterOutgoing"><?php echo gettext("mysite.settings.pmFilterOutgoing")?></label>
</div>
<div class="settings notifications">
<h3><?php echo gettext("mysite.settings.notification")?></h3>
<input class="checkbox" id="newmailnotification" name="newmailnotification" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::NEW_MAIL_NOTIFICATION)) { ?> checked="checked" <?php } ?>/>
<label for="newmailnotification"><?php echo gettext("mysite.settings.notification.newmail")?></label><br />
<input class="checkbox" id="newfriendnotification" name="newfriendnotification" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::NEW_FRIEND_NOTIFICATION)) { ?> checked="checked" <?php } ?>/>
<label for="newfriendnotification"><?php echo gettext("mysite.settings.notification.newfriend")?></label><br />
<input class="checkbox" id="friendchangednotification" name="friendchangednotification" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::FRIEND_CHANGED_NOTIFICATION)) { ?> checked="checked" <?php } ?>/>
<label for="friendchangednotification"><?php echo gettext("mysite.settings.notification.friendchanged")?></label><br />
<input class="checkbox" id="groupmembernotification" name="groupmembernotification" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::GROUP_MEMBER_NOTIFICATION)) { ?> checked="checked" <?php } ?>/>
<label for="groupmembernotification"><?php echo gettext("mysite.settings.notification.memberrequest")?></label>
</div>
<div class="settings misc">
<h3><?php echo gettext("mysite.settings.privacy")?></h3>
<input class="checkbox" id="searchengine" name="searchengine" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::SEARCHENGINE)) { ?> checked="checked" <?php } ?>/>
<label for="searchengine"><?php echo gettext("mysite.settings.searchengine")?></label><br />
<input class="checkbox" id="visibleonline" name="visibleonline" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_ONLINE)) { ?> checked="checked" <?php } ?>/>
<label for="visibleonline"><?php echo gettext("mysite.settings.visibleonline")?></label><br />
<input class="checkbox" id="visiblelastvisitor" name="visiblelastvisitor" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::VISIBLE_LAST_VISITOR)) { ?> checked="checked" <?php } ?>/>
<label for="visiblelastvisitor"><?php echo gettext("mysite.settings.visiblelastvisitor")?></label><br />
<input class="checkbox" id="tracking" name="tracking" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::TRACKING)) { ?> checked="checked" <?php } ?>/>
<label for="tracking"><?php echo gettext("mysite.settings.tracking")?></label><br />
<input class="checkbox" id="questionaire1Hide" name="questionaire1Hide" value="true" type="checkbox"<?php
if ($this->currentUser->tabQuestionaire1Hide) { ?> checked="checked" <?php } ?>/>
<label for="questionaire1Hide"><?php echo gettext("mysite.settings.questionaire1Hide")?></label><br />
<input class="checkbox" id="questionaire2Hide" name="questionaire2Hide" value="true" type="checkbox"<?php
if ($this->currentUser->tabQuestionaire2Hide) { ?> checked="checked" <?php } ?>/>
<label for="questionaire2Hide"><?php echo gettext("mysite.settings.questionaire2Hide")?></label><br />
<input class="checkbox" id="questionaire3Hide" name="questionaire3Hide" value="true" type="checkbox"<?php
if ($this->currentUser->tabQuestionaire3Hide) { ?> checked="checked" <?php } ?>/>
<label for="questionaire3Hide"><?php echo gettext("mysite.settings.questionaire3Hide")?></label><br />
<input class="checkbox" id="questionaire4Hide" name="questionaire4Hide" value="true" type="checkbox"<?php
if ($this->currentUser->tabQuestionaire4Hide) { ?> checked="checked" <?php } ?>/>
<label for="questionaire4Hide"><?php echo gettext("mysite.settings.questionaire4Hide")?></label><br />
<input class="checkbox" id="questionaire5Hide" name="questionaire5Hide" value="true" type="checkbox"<?php
if ($this->currentUser->tabQuestionaire5Hide) { ?> checked="checked" <?php } ?>/>
<label for="questionaire5Hide"><?php echo gettext("mysite.settings.questionaire5Hide")?></label>
</div>
<div class="settings misc">
<h3><?php echo gettext("mysite.settings.profilepics")?></h3>
<input class="checkbox" id="rotatePics" name="rotatePics" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::ROTATE_PICS)) { ?> checked="checked" <?php } ?>/>
<label for="rotatePics"><?php echo gettext("mysite.settings.rotatePics")?></label><br />
<input class="checkbox" id="profileWatermark" name="profileWatermark" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::PROFILE_WATERMARK)) { ?> checked="checked" <?php } ?>/>
<label for="profileWatermark"><?php echo gettext("mysite.settings.profileWatermark")?></label>
</div>
<div class="settings misc">
<h3><?php echo gettext("mysite.settings.other")?></h3>
<label for="userlanguage"><?php echo gettext("mysite.settings.userlanguage")?> </label>
<select class="text" id="userlanguage" name="userlanguage" size="1" ><?php
$languages = array();
$languages['en'] = gettext('language.en');
$languages['de'] = gettext('language.de');
$defaultLanguage = $this->sessionSettings->getValue(\vc\object\Settings::USER_LANGUAGE);
foreach ($languages as $language=>$caption)
{
if ($language == $defaultLanguage)
{
?><option value="<?php echo $language?>" selected="selected"><?php echo $caption?></option><?php
}
else
{
?><option value="<?php echo $language?>"><?php echo $caption?></option><?php
}
}
?></select><br />
<label for="distanceunit"><?php echo gettext("mysite.settings.distance")?></label>
<select class="text130" id="distanceunit" name="distanceunit" size="1"><?php
$distanceUnit = $this->sessionSettings->getValue(\vc\object\Settings::DISTANCE_UNIT);
?><option value="<?php echo \vc\object\Settings::DISTANCE_UNIT_KILOMETER?>" <?php
if ($distanceUnit == \vc\object\Settings::DISTANCE_UNIT_KILOMETER)
{
?> selected="selected"<?php
}
?>><?php echo gettext("mysite.settings.distance.kilometer")?></option><?php
?><option value="<?php echo \vc\object\Settings::DISTANCE_UNIT_MILE?>" <?php
if ($distanceUnit == \vc\object\Settings::DISTANCE_UNIT_MILE)
{
?> selected="selected"<?php
}
?>><?php echo gettext("mysite.settings.distance.mile")?></option><?php
?></select><br />
<input class="checkbox" id="plusmarker" name="plusmarker" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::PLUS_MARKER)) { ?> checked="checked" <?php } ?>/>
<label for="plusmarker"><?php echo gettext("mysite.settings.plusmarker")?></label><br />
<input class="checkbox" id="pressinterviewpartner" name="pressinterviewpartner" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::PRESS_INTERVIEW_PARTNER)) { ?> checked="checked" <?php } ?>/>
<label for="pressinterviewpartner"><?php echo gettext("mysite.settings.pressinterviewpartner")?></label><br />
<input class="checkbox" id="beta_user" name="beta_user" value="true" type="checkbox"<?php
if ($this->sessionSettings->getValue(\vc\object\Settings::BETA_USER)) { ?> checked="checked" <?php } ?>/>
<label for="beta_user"><?php echo gettext("mysite.settings.beta_user")?></label><br />
</div>
<div class="settings buttons">
<button type="reset"><?php echo gettext('mysite.settings.cancel')?></button>
<button type="submit"><?php echo gettext('mysite.settings.confirm')?></button>
</div>
</form>