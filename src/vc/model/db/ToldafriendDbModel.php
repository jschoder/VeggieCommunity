<?php
namespace vc\model\db;

class ToldafriendDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_toldafriend';
    const OBJECT_CLASS = '\\vc\object\\ToldAFriend';

    public function add(
        $sender,
        $reciever1,
        $reciever2,
        $reciever3,
        $reciever4,
        $reciever5,
        $reciever6,
        $subject,
        $message,
        $profileId
    ) {
        $query = 'INSERT INTO vc_toldafriend SET
                  sender = ?,
                  reciever1 = ?,
                  reciever2 = ?,
                  reciever3 = ?,
                  reciever4 = ?,
                  reciever5 = ?,
                  reciever6 = ?,
                  subject = ?,
                  body = ?,
                  created = ?,
                  profileid = ?';
        $success = $this->getDb()->executePrepared(
            $query,
            array(
                $sender,
                $reciever1,
                $reciever2,
                $reciever3,
                $reciever4,
                $reciever5,
                $reciever6,
                $subject,
                $message,
                date('Y-m-d H:i:s'),
                $profileId
            )
        );
        return $success;
    }
}
