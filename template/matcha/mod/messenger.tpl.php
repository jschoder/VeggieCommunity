<h1>Mod Messenger</h1>
<div class="notifyInfo">
<p><strong>Replacements:</strong></p>
<ul>
<li>%NICKNAME%</li>
</ul>
<p><strong>Default filters:</strong></p>
<ul>
<li>Active only fixed filter</li>
<li>German: lang.value='de'</li>
<li>English: lang.value='en'</li>
</ul>
<p><strong>Default joins:</strong></p>
<ul>
<li>Interview: INNER JOIN vc_setting ON vc_setting.profileid = vc_profile.id AND vc_setting.field = 40 AND vc_setting.value = '1'</li>
</ul>
</div><?php
echo $this->renderForm($this->form);
if (!empty($this->links)) {
echo '<p>' . $this->links . '</p>';
}