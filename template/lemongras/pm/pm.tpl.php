<div class="messageListing">
<div class="tab-pane clearfix active" id="messages">
<div id="pmThreads" class="chooseMessage scrollbar">
<form class="threadFilter" action="#">
<ul>
<li>
<input name="namefilter" class="jAutoSubmit" placeholder="<?php echo gettext('pm.filter.naming') ?>" type="text" />
</li>
<li>
<input id="threadFilterNew" type="checkbox" name="newfilter" class="jAutoSubmit" value="1" />
<label for="threadFilterNew"><?php echo gettext('pm.filter.unread') ?></label>
</li>
</ul>
</form>
<ul class="jPmThreads">
<li class="jLoading loading"><?php echo gettext('pm.loadingContacts'); ?></li>
</ul>
</div> <!-- #inboxList -->
<div id="inboxList" class="viewMessage">
<div id="inboxControl" class="clearfix header">
<div class="jNotifications"></div>
<div class="actions">
<?php echo gettext('pm.actions') ?>:
<a class="block" href="#" title="<?php echo gettext('pm.block') ?>"></a>
<a class="deleteConversation" href="#" title="<?php echo gettext('pm.deleteConversation') ?>"></a><?php
if ($this->plusLevel >= vc\object\Plus::PLUS_TYPE_STANDARD) {
?><a class="filter" href="#" title="<?php echo gettext('pm.filter') ?>"></a>
<a class="pdfExport" href="#" target="_blank" title="<?php echo gettext('pm.pdfExport') ?>"></a><?php
} else {
?><a class="filter" href="<?php echo $this->path ?>plus/" title="<?php echo gettext('pm.filter') ?> (<?php echo gettext('pm.plusOnly') ?>)"></a>
<a class="pdfExport" href="<?php echo $this->path ?>plus/" title="<?php echo gettext('pm.pdfExport') ?> (<?php echo gettext('pm.plusOnly') ?>)"></a><?php
}
?></div>
<div class="recipients">
<span><?php echo gettext('pm.conversationWith') ?></span> <a class="jConversation" href="#"></a>
</div><?php
$startDate = new DateTime('2001-01-02');
$today = new DateTime('now');
$interval = $startDate->diff($today)->format('%a') * -1;
if ($this->plusLevel >= vc\object\Plus::PLUS_TYPE_STANDARD) {
?><div class="filterDialog" style="display:none">
<form action="#" method="post">
<div>
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
<span class="icon">
<span class="fa fa-time"></span>
</span>
<input class="time" class="form-control" name="from[time]" />
</div>
</div>
<div>
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
<span class="icon">
<span class="fa fa-time"></span>
</span>
<input class="time" class="form-control" name="to[time]" />
</div>
</div>
<div>
<label for="filter-text"><?php echo gettext('pm.filter.text') ?></label>
<input class="text" id="filter-text" name="textfilter" type="text" />
</div>
<div>
<button type="submit"><?php echo gettext('pm.filter.submit') ?></button>
<button class="secondary" type="reset"><?php echo gettext('pm.filter.reset') ?></button>
</div>
</form>
</div><?php
}
?></div>
<div class="details jPmMessages scrollbar">
<div class="jLoading loading"><?php echo gettext('pm.loadingMessages'); ?></div>
</div> <!-- .details -->
<form class="jReplyForm replyForm">
<div class="form-group">
<textarea class="form-control jAutoHeight" placeholder="<?php echo gettext('pm.typeMessage') ?>" rows="2"></textarea>
<button class="btn btn-primary" type="submit"><?php echo gettext('pm.send') ?></button>
<a class="saveDraft" href="#"><?php echo gettext('pm.saveDraft') ?></a>
<span class="jLoading" style="display:none;" />
</div>
</form> <!-- .replyForm -->
</div> <!-- #inboxList -->
</div> <!-- #messages -->
</div>
<script id="jPmThreadsTemplate" type="text/template">
<li id="jThread{{contact.id}}" class="jThread {{#isNew}}new{{/isNew}} {{#activeThread}}active{{/activeThread}}">
<div class="clearfix">
<a href="#{{contact.id}}" class="user">
{{#contact.picture}}
<img src="/user/picture/crop/74/74/{{contact.picture}}" alt="" width="45" height="45" />
{{/contact.picture}}
{{^contact.picture}}
{{#contact.isActive}}
<img src="<?php echo $this->imagesPath ?>default-thumb-{{contact.gender}}.png" alt="" width="45" height="45" />
{{/contact.isActive}}
{{^contact.isActive}}
<img src="<?php echo $this->imagesPath ?>default-thumb-deleted.png" alt="" width="45" height="45" />
{{/contact.isActive}}
{{/contact.picture}}
</a>
<div class="details">
<a class="nickname{{#contact.isReal}} real{{/contact.isReal}}{{#contact.isPlus}} plus{{/contact.isPlus}}" href="#{{contact.id}}">{{contact.nickname}}</a>
<a class="textpreview" href="#{{contact.id}}">{{{lastMessage}}}</a>
<!-- <h3><a href="#{{contact.id}}">{{lastMessage}}</a></h3> -->
<span class="date"><span class="jAgo" data-ts="{{created}}">{{created}}</span></span>
</div>
<span class="replied"><i class="glyphicon glyphicon-share-alt"></i></span>
</div>
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
<div id="jMessage{{id}}" class="jMessage {{#received}}received{{/received}}{{^received}}sent{{/received}} clearfix">
<div class="image">
<a href="<?php echo $this->path ?>user/view/{{senderid}}">
{{#sender.picture}}
<img src="/user/picture/crop/74/74/{{sender.picture}}" alt="" width="65" height="65" />
{{/sender.picture}}
{{^sender.picture}}
{{#sender.isActive}}
<img src="<?php echo $this->imagesPath ?>default-thumb-{{sender.gender}}.png" alt="" width="65" height="65" />
{{/sender.isActive}}
{{^sender.isActive}}
<img src="<?php echo $this->imagesPath ?>default-thumb-deleted.png" alt="" width="65" height="65" />
{{/sender.isActive}}
{{/sender.picture}}
</a>
</div>
<div class="info clearfix">
<a class="user{{#sender.isReal}} real{{/sender.isReal}}{{#sender.isPlus}} plus{{/sender.isPlus}}" href="<?php echo $this->path ?>user/view/{{senderid}}">{{sender.nickname}}</a>
<span class="date"><span class="jAgo" data-ts="{{created}}">{{created}}</span></span>
<div class="context">
<a class="show-context">...</a>
<div class="context-menu">
<ul>
{{#received}}
<li><a href="#" class="flag" data-pm-id="{{id}}"><?php echo gettext('mailbox.markspam') ?></a></li>
{{/received}}
<li><a href="#" class="delete" data-pm-id="{{id}}"><?php echo gettext('mailbox.trashmail') ?></a></li>
</ul>
</div>
</div>
</div>
<div class="text">
{{#subject}}<p><strong>{{subject}}</strong></p>{{/subject}}
<p>{{{body}}}</p>
{{#preFlagMessage}}
<div class="notifyWarn">
{{{preFlagMessage}}}
<a href="#" class="flag" data-pm-id="{{id}}"><?php echo gettext('mailbox.warning.reportspam') ?></a>
</div>
{{/preFlagMessage}}
</div>
</div>
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
