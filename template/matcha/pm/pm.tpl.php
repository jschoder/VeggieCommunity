<aside id="inboxControl">
<nav class="actions">
<?php echo gettext('pm.actions') ?>:
<a class="block" href="#" title="<?php echo gettext('pm.block') ?>"><span><?php echo gettext('pm.block') ?></span></a>
<a class="deleteConversation" href="#" title="<?php echo gettext('pm.deleteConversation') ?>"><span><?php echo gettext('pm.deleteConversation') ?></span></a> <?php
if ($this->plusLevel >= vc\object\Plus::PLUS_TYPE_STANDARD) {
?><a class="filter" href="#" title="<?php echo gettext('pm.filter') ?>"><span><?php echo gettext('pm.filter') ?></span></a>
<a class="pdfExport" href="#" target="_blank" title="<?php echo gettext('pm.pdfExport') ?>"><span><?php echo gettext('pm.pdfExport') ?></span></a><?php
} else {
?><a class="filter" href="<?php echo $this->path ?>plus/" title="<?php echo gettext('pm.filter') ?> (<?php echo gettext('pm.plusOnly') ?>)"><span><?php echo gettext('pm.filter') ?> (<?php echo gettext('pm.plusOnly') ?>)</span></a>
<a class="pdfExport" href="<?php echo $this->path ?>plus/" title="<?php echo gettext('pm.pdfExport') ?> (<?php echo gettext('pm.plusOnly') ?>)"><span><?php echo gettext('pm.pdfExport') ?> (<?php echo gettext('pm.plusOnly') ?>)</span></a><?php
}
?></nav>
<div class="recipients">
<span><?php echo gettext('pm.conversationWith') ?></span> <a href="#" class="jConversation"></a>
</div><?php
if ($this->plusLevel >= vc\object\Plus::PLUS_TYPE_STANDARD):
$startDate = new DateTime('2001-01-02');
$today = new DateTime('now');
$interval = $startDate->diff($today)->format('%a') * -1;
?><div class="filterDialog" style="display:none">
<form action="#" method="post">
<ul>
<li>
<label for="filter-from-date"><?php echo gettext('pm.filter.from') ?></label><?php
echo $this->element(
'date',
array(
'locale' => $this->locale,
'class' => 'date',
'dateStart' => $interval,
'dateEnd' => '0',
'id' => 'filter-from-date',
'name' => 'from[date]',
'default' => null
)
);
?><div class="clockpicker">
<input class="time" id="filter-from-time" class="form-control" name="from[time]" type="text" /><label class="icon" for="filter-from-time"></label>
</div>
</li>
<li>
<label for="filter-to-date"><?php echo gettext('pm.filter.to') ?></label><?php
echo $this->element(
'date',
array(
'locale' => $this->locale,
'class' => 'date',
'dateStart' => $interval,
'dateEnd' => '0',
'id' => 'filter-to-date',
'name' => 'to[date]',
'default' => null
)
);
?><div class="clockpicker">
<input class="time" id="filter-to-time" class="form-control" name="to[time]" type="text" /><label class="icon" for="filter-to-time"></label>
</div>
</li>
<li>
<label for="filter-text"><?php echo gettext('pm.filter.text') ?></label>
<input id="filter-text" name="textfilter" type="text" />
</li>
<li>
<button type="submit"><?php echo gettext('pm.filter.submit') ?></button>
<button class="secondary" type="reset"><?php echo gettext('pm.filter.reset') ?></button>
</li>
</ul>
</form>
</div><?php
endif;
?></aside>
<div id="inboxList">
<div class="jNotifications"></div>
<div class="details jPmMessages scrollbar">
<div class="jLoading"><?php echo gettext('pm.loadingMessages'); ?></div>
</div>
<form class="jReplyForm">
<ul>
<li>
<textarea placeholder="<?php echo gettext('pm.typeMessage') ?>" class="jAutoHeight" rows="1"></textarea>
</li>
<li>
<button type="submit"><?php echo gettext('pm.send') ?></button>
<div class="jLoading" style="display:none"></div>
<a class="saveDraft" href="#"><?php echo gettext('pm.saveDraft') ?></a>
</li>
</ul>
</form>
</div>
<script id="jPmThreadsTemplate" type="text/template">
<li class="jThread{{#isNew}} new{{/isNew}}{{#activeThread}} active{{/activeThread}}" id="jThread{{contact.id}}">
<a href="#{{contact.id}}">
{{#contact.picture}}
<img alt="" class="user" src="/user/picture/crop/74/74/{{contact.picture}}" />
{{/contact.picture}}
{{^contact.picture}}
{{#contact.isActive}}
<img alt="" class="user" src="<?php echo $this->imagesPath ?>thumb/default-thumb-{{contact.gender}}.png"  />
{{/contact.isActive}}
{{^contact.isActive}}
<img alt="" class="user" src="<?php echo $this->imagesPath ?>thumb/default-thumb-deleted.png" />
{{/contact.isActive}}
{{/contact.picture}}
<div class="details">
<div class="nickname{{#contact.isReal}} real{{/contact.isReal}}{{#contact.isPlus}} plus{{/contact.isPlus}}">{{contact.nickname}}</div>
<div class="textpreview">{{lastMessage}}</div>
<div class="date"><span data-ts="{{created}}" class="jAgo"></span></div>
</div>
</a>
</li>
</script>
<?php /* Valid types: success, info, warning, error */ ?>
<script id="jPmNotificationTemplate" type="text/template">
<div class="{{type}}">
{{text}}
{{#undo}}<a class="undo" href="#" data-action="{{undo}}"><?php echo gettext('js.undo') ?></a>{{/undo}}
<span class="jClose close" title="<?php echo gettext('pm.notification.close') ?>"></span>
</div>
</script>
<script id="jPmMessagesTemplate" type="text/template">
<article class="bubbleW jMessage{{#received}}{{/received}}{{^received}} my{{/received}}" id="jMessage{{id}}">
<aside>
<a class="label" href="<?php echo $this->path ?>user/view/{{senderid}}/">
{{#sender.picture}}
<img alt="" src="/user/picture/crop/74/74/{{sender.picture}}" />
{{/sender.picture}}
{{^sender.picture}}
{{#sender.isActive}}
<img alt="" src="<?php echo $this->imagesPath ?>thumb/default-thumb-{{sender.gender}}.png" />
{{/sender.isActive}}
{{^sender.isActive}}
<img alt="" src="<?php echo $this->imagesPath ?>thumb/default-thumb-deleted.png" />
{{/sender.isActive}}
{{/sender.picture}}
</a>
</aside>
<div class="bubble">
<header>
<a class="{{#sender.isReal}}real{{/sender.isReal}}{{#sender.isPlus}} plus{{/sender.isPlus}}" href="<?php echo $this->path ?>user/view/{{senderid}}/">{{sender.nickname}}</a>
<span class="jAgo" data-ts="{{created}}"></span>
<aside class="popup">
<span class="context jTrigger" tabindex="0" onclick="void(0)"></span>
<nav class="menu">
{{#received}}
<a href="#" class="flag" data-pm-id="{{id}}"><?php echo gettext('mailbox.markspam') ?></a>
{{/received}}
<a href="#" class="delete" data-pm-id="{{id}}"><?php echo gettext('mailbox.trashmail') ?></a>
</nav>
</aside>
</header>
{{#subject}}<p><strong>{{subject}}</strong></p>{{/subject}}
<p>{{{body}}}</p>
{{#preFlagMessage}}
<div class="notifyWarn">
{{{preFlagMessage}}}
<ul class="list">
<li><a href="#" class="flag" data-pm-id="{{id}}"><?php echo gettext('mailbox.warning.reportspam') ?></a></li>
<li><a href="#"
class="jSet"
data-set="settings/<?php echo \vc\object\Settings::PM_FILTER_INCOMING ?>/0/"
data-hide=".notifyWarn"
data-hide-message="<?php echo gettext('mailbox.warning.hidereportspam.confirmed') ?>"><?php echo gettext('mailbox.warning.hidereportspam') ?></a></li>
</ul>
</div>
{{/preFlagMessage}}
</div>
</article>
</script>
<script id="jPmDraftTemplate" type="text/template">
<article id="draft-{{id}}" class="notifyInfo draft" data-type="{{type}}"{{#id}} data-id="{{id}}"{{/id}}>
<strong><?php echo gettext('pm.draft') ?></strong>
<p>{{text}}</p>
<aside>
<nav>
<a class="edit" href="#"><?php echo gettext('pm.draft.edit') ?></a>
<a class="delete" href="#"><?php echo gettext('pm.draft.delete') ?></a>
</nav>
</aside>
</article>
</script><?php
$this->addScript(
'var initialThreads = ' . json_encode($this->threads) . ';' .
'vc.pm.init("' .
$this->design . '", ' .
intval($this->defaultContact) .  ',' .
intval($this->filterOutgoing) .
', initialThreads);'
);
