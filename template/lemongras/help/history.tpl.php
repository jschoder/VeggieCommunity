<?php
if ($this->notification):
echo $this->element('notification', array('notification' => $this->notification));
endif;
?><h1><?php echo gettext('menu.help') ?></h1><?php
echo $this->element('tabs/help',
array('path' => $this->path,
'site' => $this->site));
?><h2><?php
if (empty($this->ticket->subject)):
echo gettext('help.history.emptySubject');
else:
echo prepareHTML($this->ticket->subject);
endif;
echo ' (' . $this->ticketCategories[$this->ticket->category] . ')';
?></h2><?php
foreach ($this->ticketMessages as $ticketMessage):
?><div class="thread"><?php
if ($ticketMessage->byAdmin):
?><a class="image" href="<?php echo $this->path ?>help/support/">
<img alt="" class="image" src="/img/matcha/thumb/default-thumb-o.png" width="50" height="50" />
</a><?php
else:
?><a class="image" href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'width' => 50,
'height' => 50));
?></a><?php
endif;
$class = 'threadArticle';
if ($ticketMessage->byAdmin === 0):
$class .= ' myThreadArticle';
endif;
?><div class="<?php echo $class ?>">
<div class="user"><?php
if ($ticketMessage->byAdmin):
?><a class="real" href="<?php echo $this->path ?>help/support/"><?php
echo gettext('help.history.supportTeam');
?></a> <?php
else:
$class = array();
if ($this->currentUser->realMarker):
$class[] = 'real';
endif;
if ($this->currentUser->plusMarker):
$class[] = 'plus';
endif;
?><a <?php if(!empty($class)) { echo 'class="' . implode(' ', $class) . '" '; } ?>href="/de/user/view/<?php echo $this->currentUser->id ?>/"><?php
echo prepareHTML($this->currentUser->nickname)
?></a> <?php
endif;
?><span class="date">(<span class="jAgo" data-ts="<?php echo strtotime($ticketMessage->createdAt) ?>"></span>)</span>
</div>
<div class="body">
<p><?php
echo prepareHTML($ticketMessage->body);
?></p>
</div>
<div class="actions">
</div>
</div>
<div class="clearfix"></div>
</div><?php
endforeach;
?><div id="newthread" class="clearfix">
<?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation(),
'width' => 50,
'height' => 50));
?>
<div class="formWrapper">
<div class="form">
<form action="<?php echo $this->path ?>help/support/reply/" method="post">
<input type="hidden" name="ticket_id" value="<?php echo $this->ticket->hashId ?>" />
<textarea placeholder="<?php echo gettext('help.history.reply.body') ?>" rows="5" class="jAutoHeight" name="body"></textarea>
<button title="<?php echo gettext('help.history.reply.confirm')?>" class="save" type="submit">
<span><?php echo gettext('help.history.reply.confirm')?></span>
</button>
</form>
</div>
</div>
</div>