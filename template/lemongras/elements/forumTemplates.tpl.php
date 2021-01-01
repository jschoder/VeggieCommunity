<script id="jForumThreadEditTemplate" type="text/template">
<div class="threadForm">
<div class="form">
<form action="#">
<input type="hidden" name="id" value="{{id}}" />
<input type="text" name="subject" value="{{subject}}" maxlength="255" />
<textarea name="body" class="jAutoHeight" rows="3">{{body}}</textarea>
<div class="loader" style="display:none"></div>
<button class="save" type="submit" title="<?php echo gettext('forum.thread.confirm')?>">
<span><?php echo gettext('forum.thread.confirm')?></span>
</button>
<button class="cancel secondary" title="<?php echo gettext('forum.thread.cancel')?>">
<span><?php echo gettext('forum.thread.cancel')?></span>
</button>
</form>
</div>
</div>
</script>
<script id="jForumCommentEditTemplate" type="text/template">
<div class="threadCommentForm">
<div class="form">
<form action="#">
<input type="hidden" name="id" value="{{id}}" title="<?php echo gettext('forum.thread.subject') ?>" />
<textarea name="body" class="jAutoHeight" title="<?php echo gettext('forum.thread.body') ?>" rows="3">{{body}}</textarea>
<div class="loader" style="display:none"></div>
<button class="save" type="submit" title="<?php echo gettext('forum.comment.confirm')?>">
<span><?php echo gettext('forum.comment.confirm')?></span>
</button>
<button class="cancel secondary" title="<?php echo gettext('forum.comment.cancel')?>">
<span><?php echo gettext('forum.comment.cancel')?></span>
</button>
</form>
</div>
</div>
</script>