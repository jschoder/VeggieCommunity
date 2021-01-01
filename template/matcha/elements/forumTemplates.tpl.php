<script id="jForumThreadEditTemplate" type="text/template">
<div class="threadForm">
<div class="bubble form">
<form action="#">
<input type="hidden" name="id" value="{{id}}" />
<ul>
<li><input type="text" name="subject" value="{{subject}}" maxlength="255" /></li>
<li><textarea name="body" class="jAutoHeight" rows="3">{{body}}</textarea></li>
</ul>
<div class="actions">
<div class="loader jLoading hidden"></div>
<button class="save" type="submit" title="<?php echo gettext('forum.thread.confirm')?>"></button>
<button class="cancel secondary" title="<?php echo gettext('forum.thread.cancel')?>"></button>
</div>
</form>
</div>
</div>
</script>
<script id="jForumCommentEditTemplate" type="text/template">
<div class="threadCommentForm">
<div class="bubble form">
<form action="#">
<input type="hidden" name="id" value="{{id}}" title="<?php echo gettext('forum.thread.subject') ?>" />
<ul>
<li><textarea name="body" class="jAutoHeight" title="<?php echo gettext('forum.thread.body') ?>" rows="3">{{body}}</textarea></li>
</ul>
<div class="actions">
<div class="loader jLoading hidden"></div>
<button class="save" type="submit" title="<?php echo gettext('forum.comment.confirm')?>"></button>
<button class="cancel secondary" title="<?php echo gettext('forum.comment.cancel')?>"></button>
</div>
</form>
</div>
</div>
</script>