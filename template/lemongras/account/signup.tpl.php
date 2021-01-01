<?php if (!empty($this->locale) && $this->locale === 'de'): ?>
<div class="newsitem notifyWarn">
    <p>Die Seite wird zum 1. Januar abgeschaltet. Neuanmeldungen sind daher nicht mehr möglich.</p>
</div>
<?php endif; ?>
<?php if (!empty($this->locale) && $this->locale === 'en'): ?>
<div class="newsitem notifyWarn">
    <p>The site will be closed on January 1st. Creating new profiles has been deactivated for that reason.</p>
</div>
<?php endif; ?>
<?php
/*
<h1><?php echo gettext("signup.title") ?></h1>
<div id="content"><?php
echo $this->renderForm($this->form);
?></div>
*/
