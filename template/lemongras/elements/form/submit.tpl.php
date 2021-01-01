<dt><?php
if (!empty($this->caption)) {
if (empty($this->primaryName)) {
?><button class="submit" type="submit"><?php echo $this->caption ?></button><?php
} else {
?><button class="submit" type="submit" name="<?php echo $this->primaryName ?>" value="1"><?php echo $this->caption ?></button><?php
}
}
if (!empty($this->secondaryButtons)) {
foreach ($this->secondaryButtons as $name => $caption) {
?><button class="submit secondary" type="submit" name="<?php echo $name ?>" value="1"><?php echo $caption ?></button><?php
}
}
?></dt>