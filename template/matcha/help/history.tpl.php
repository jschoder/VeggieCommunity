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
?><article class="bubbleW jThread">
<aside><?php
if ($ticketMessage->byAdmin):
?><a class="label" href="<?php echo $this->path ?>help/support/">
<img alt="" class="image" src="/img/matcha/thumb/default-thumb-o.png" />
</a><?php
else:
?><a class="label" href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture));
?></a><?php
endif;
?></aside><?php
$class = 'bubble threadArticle';
if ($ticketMessage->byAdmin === 0):
$class .= ' my';
endif;
?><div class="<?php echo $class ?>">
<header><?php
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
?><span class="jAgo" data-ts="<?php echo strtotime($ticketMessage->createdAt) ?>"></span>
</header>
<p><?php
echo prepareHTML($ticketMessage->body);
?></p>
</div>
</article><?php
endforeach;
?><article class="bubbleW" id="newthread">
<aside>
<a href="<?php echo $this->path ?>user/view/<?php echo $this->currentUser->id ?>/" class="label"><?php
echo $this->element('picture.crop',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'picture' => $this->ownPicture,
'title' => $this->currentUser->getHtmlLocation()));
?></a>
</aside>
<div class="bubble form">
<form action="<?php echo $this->path ?>help/support/reply/" method="post">
<input type="hidden" name="ticket_id" value="<?php echo $this->ticket->hashId ?>" />
<ul>
<li>
<textarea placeholder="<?php echo gettext('help.history.reply.body') ?>" rows="5" class="jAutoHeight" name="body"></textarea>
<label class="help"><?php echo gettext('help.history.reply.help') ?></label>
</li>
</ul>
<div class="actions">
<div class="jLoading loader hidden"></div>
<button title="<?php echo gettext('help.history.reply.confirm')?>" class="save" type="submit"></button>
</div>
</form>
</div>
</article>