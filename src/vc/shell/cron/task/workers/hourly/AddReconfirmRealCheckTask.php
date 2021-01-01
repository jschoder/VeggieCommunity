<?php
namespace vc\shell\cron\task\workers\hourly;

class AddReconfirmRealCheckTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        // Remove deleted pictures from real check
        $query = 'DELETE vc_real_picture FROM vc_real_picture
                  LEFT JOIN vc_picture ON vc_real_picture.picture_id = vc_picture.id
                  WHERE vc_picture.id IS NULL';
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        // Set status to reopened for confirmed real checks with deleted pictures
        $query = 'UPDATE vc_real_check
                  LEFT JOIN vc_real_picture ON vc_real_picture.real_check_id = vc_real_check.id
                  SET vc_real_check.status = ' . \vc\object\RealCheck::STATUS_REOPENED . '
                  WHERE vc_real_picture.picture_id IS NULL AND vc_real_check.status = ' .
                  \vc\object\RealCheck::STATUS_CONFIRMED;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }
    }
}
