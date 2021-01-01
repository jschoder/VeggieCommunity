<p>du hast eine Nachricht von <?php echo $this->prepareHtml($this->username) ?> erhalten.<br />
   <a href="https://www.veggiecommunity.org/de/<?php echo $this->pmLink ?>">https://www.veggiecommunity.org/de/<?php echo $this->pmLink ?></a></p>
<div class="bubbleW">
    <aside>
        <a class="label" href="https://www.veggiecommunity.org/de/<?php echo $this->userLink ?>">
            <img alt="" src="cid:user-<?php echo $this->userId ?>">
        </a>
    </aside>
    <div class="bubble">
        <p><?php echo nl2br($this->prepareHtml($this->message)) ?></p>
    </div>
</div>