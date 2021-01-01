<?php
namespace vc\object;

/*
 * RecipientStatus:
 *  -6 = profile deleted
 *  -5 = trash cleaned
 *  -4 = spam suspicion denied
 *  -3 = spam + deleted
 *  -2 = spam suspect + deleted
 *  -1 = deleted
 *     0 = not sent
 *     1 = new
 *     2 = read
 *
 * Message senderstatus:
 * -2: profile deleted
 * -1: deleted
 * 1: sent
 */
class Mail
{
    const RECIPIENT_STATUS_PROFILE_DELETED = -6;
    const RECIPIENT_STATUS_TRASH_CLEANED = -5;
    const RECIPIENT_STATUS_SPAM_DENIED = -4;
    const RECIPIENT_STATUS_SPAM_CONFIRMED = -3;
    const RECIPIENT_STATUS_SPAM_SUSPECT = -2;
    const RECIPIENT_STATUS_DELETED = -1;
    const RECIPIENT_STATUS_UNSENT = 0;
    const RECIPIENT_STATUS_NEW = 1;
    const RECIPIENT_STATUS_READ = 2;
    
    const SENDER_STATUS_SENT = 1;
    const SENDER_STATUS_DELETED = -1;
    const SENDER_STATUS_PROFILE_DELETED = -2;
    
    
    public $id;
    public $recipient;
    public $recipientNickname;
    public $recipientStatus;
    public $recipientReplied;
    public $sender;
    public $senderNickname;
    public $senderEmail;
    public $senderStatus;
    public $subject;
    public $body;
    public $created;
    public $hideEmail;
}
