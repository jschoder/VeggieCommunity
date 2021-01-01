<section id="pmThreads" class="scrollbar">
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
<li class="jLoading"><?php echo gettext('pm.loadingContacts'); ?></li>
</ul>
</section>