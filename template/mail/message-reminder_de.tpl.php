<p>du hast seit deinem letzten Login eine neue Nachricht bekommen, die mindestens seit ein paar Tagen ungelesen und unbeantwortet in deiner Inbox liegt.</p>
<ul class="thumblist"><?php
    foreach ($this->sender as $senderId => $senderName):
        ?><li>
            <a href="https://www.veggiecommunity.org/de/pm/#<?php echo $senderId ?>"><img alt="" src="cid:user-<?php echo $senderId ?>"></a>
            <a class="label" href="https://www.veggiecommunity.org/de/pm/#<?php echo $senderId ?>"><?php echo prepareHTML($senderName) ?></a>
        </li><?php
    endforeach;
?></ul>