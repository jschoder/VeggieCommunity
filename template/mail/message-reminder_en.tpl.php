<p>you have received a message since your last login that is waiting in your inbox for at least a few days.</p>
<ul class="thumblist"><?php
    foreach ($this->sender as $senderId => $senderName):
        ?><li>
            <a href="https://www.veggiecommunity.org/en/pm/#<?php echo $senderId ?>"><img alt="" src="cid:user-<?php echo $senderId ?>"></a>
            <a class="label" href="https://www.veggiecommunity.org/en/pm/#<?php echo $senderId ?>"><?php echo prepareHTML($senderName) ?></a>
        </li><?php
    endforeach;
?></ul>