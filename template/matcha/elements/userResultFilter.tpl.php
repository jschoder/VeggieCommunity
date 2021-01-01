<span class="divider"></span>
<form action="<?php echo $this->path ?>user/result/" method="get" accept-charset="UTF-8"><?php
foreach ($this->requestQuery as $key=>$value):
if ($key != "limit"):
if (is_array($value)):
foreach ($value as $arrayValue):
?><input name="<?php echo $key?>[]" value="<?php echo prepareFormValue($arrayValue)?>" type="hidden" /><?php
endforeach;
else:
?><input name="<?php echo $key?>" value="<?php echo prepareFormValue($value)?>" type="hidden" /><?php
endif;
endif;
endforeach;
?><select name="limit" size="1" onchange="submit()" title="<?php echo gettext('result.limitation.title') ?>"><?php
foreach (array(12, 24, 36, 48, 60, 90, 120, 150, 180, 250, 300, 450) as $value):
echo("<option");
if ($value == $this->filterSize):
echo " selected=\"selected\"";
endif;
echo(">" . $value . "</option>");
endforeach;
?></select>
<?php echo gettext('result.totalSizeOf') . ' ' . $this->pagination->getTotalCount() ?>
</form>