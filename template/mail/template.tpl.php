<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title></title>
    <style type="text/css">
        *{box-sizing:border-box}
        body{-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100% !important;height:100%;line-height:1.6em;font-size:16px}
        body{background-color:#f4f5f2}
        a{color:#90b530;font-weight:bold;text-decoration:none}
        <?php if (strpos($this->mailContent, 'bubbleW') !== FALSE): ?>
        .bubbleW{display:block;max-width:40em;margin:0 auto .5em}
        .bubbleW:after{content:"";display:table;clear:both}
        .bubbleW .label{white-space:no-wrap;float:left}
        .bubbleW .label img{white-space:no-wrap;width:50px;height:50px;box-shadow:none}
        .bubble{position:relative;float:left;width:255px;width:calc(100% - 66px);min-height:53px;margin-left:15px;padding:.5em;margin-bottom:.5em;background-color:#fafafa;border:#b9b9bf solid 1px;box-shadow:none;border-radius:.5em}
        .bubble p{word-wrap:break-word;margin:0}
        .bubble:after{content:'';position:absolute;border-style:solid;border-width:9px 9px 9px 0;border-color:transparent #fafafa;display:block;width:0;z-index:9;left:-9px;top:17px}
        .bubble:before{content:'';position:absolute;border-style:solid;border-width:9px 9px 9px 0;border-color:transparent #b9b9bf;display:block;width:0;z-index:8;left:-10px;top:17px}
        /* :TODO: JOE - use it or loose it */
        .bubbleW .bubble.my{border-color:#90b530}
        .bubbleW .bubble.my:before{border-color:transparent #90b530}
        .bubbleW .bubble.mod{border-color:#fe7200}
        <?php endif; ?>
        <?php if (strpos($this->mailContent, 'thumblist') !== FALSE): ?>
        ul.thumblist{list-style:none;padding:0;margin:0 0 1em}
        ul.thumblist:after{content:"";display:table;clear:both}
        ul.thumblist li{float:left;position:relative;margin:0 .4em .4em 0}
        ul.thumblist a{display:block}
        ul.thumblist img{width:5em;box-shadow:none;padding:2px;border-radius:.3em;border:1px solid #b9b9bf}
        ul.thumblist img:hover{border-color:90b530}
        ul.thumblist .label{position:absolute;left:3px;right:3px;bottom:3px;font-size:.7em;line-height:1.2em;overflow:hidden;padding:.3em .4em;white-space:nowrap;font-weight:normal;background-color:#fafafa;color:#333;opacity:.7}
        ul.thumblist li:hover .label{opacity:.85}
        <?php endif; ?>
    </style>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage"
      style="font-family:Arial, Verdana, sans-serif;box-sizing:border-box;font-size:16px;-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100% !important;height:100%;line-height:1.6em;background-color:#f4f5f2;margin:0"
      bgcolor="#F5F5F5">
<div style="background-color:#90b530;box-shadow:0 1px 0 0 #6a8f0a, 0 2px 0 0 #b7b7b7, 0 3px 0 0 #eaeaea;margin-bottom:0.6em;padding:0.4em">
    <img style="width:59px" src="cid:vc-logo-png" alt="VeggieCommunity.org" />
</div>
<table class="body-wrap" style="width:100%;margin:0">
    <tr>
        <td class="container" width="600" style="display:block !important;max-width:600px !important;clear:both !important;margin:0 auto;color:#333;margin-bottom:0.6em" valign="top">
            <?php echo gettext('mail.greeting') ?> <span style="font-weight:bold"><?php echo $this->prepareHtml($this->recipientName) ?></span>,<br />
            <?php echo $this->mailContent ?>
            <?php if ($this->signOff): ?>
                <?php echo gettext('mail.signoff') ?><br />
                Joachim
            <?php endif; ?>
        </td>
    </tr>
</table>
<div style="background-color:#dadae8;box-shadow:0 -1px 0 0 #b4b4c2, 0 -2px 0 0 #b7b7b7, 0 -3px 0 0 #eaeaea;padding:0.6em;color:#333;font-size:0.8em">
    <a href="https://www.veggiecommunity.org/<?php echo $this->locale ?>">https://www.veggiecommunity.org/<?php echo $this->locale ?></a><?php
    echo ' - ' . gettext('header.motto');

    if ($this->unsubscribeInfo):
        if ($this->locale === 'de'):
            ?><br />Falls du keine weiteren E-Mail-Benachrichtigungen erhalten möchtest kannst du sie unter dem folgenden Link deaktivieren:
            <a href="https://www.veggiecommunity.org/de/account/settings/">https://www.veggiecommunity.org/de/account/settings/</a><?php
        else:
            ?><br />If you don't want to recieve e-mail-notifications you can deactivate them under
            <a href="https://www.veggiecommunity.org/en/account/settings/">https://www.veggiecommunity.org/en/account/settings/</a><?php
        endif;
    endif;

    if ($this->locale === 'de'):
        ?><br />Passwort vergessen oder keinen Zugriff auf dein Profil?
        <a href="https://www.veggiecommunity.org/de/account/rememberpassword/?email=<?php echo json_encode($this->recipientEmail) ?>">Du kannst dein Passwort hier zurücksetzen: https://www.veggiecommunity.org/de/account/rememberpassword/?email=<?php echo json_encode($this->recipientEmail) ?></a><?php
    else:
        ?><br />Forgot your password?
        <a href="https://www.veggiecommunity.org/en/account/rememberpassword/?email=<?php echo json_encode($this->recipientEmail) ?>">You can reset your password here: https://www.veggiecommunity.org/en/account/rememberpassword/?email=<?php echo json_encode($this->recipientEmail) ?></a><?php
    endif;

?></div>
</body>
</html>
