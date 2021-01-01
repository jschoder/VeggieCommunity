<?php
namespace vc\model\db;

class SettingsDbModel extends AbstractDbModel
{
    public function getSettings($profileId = 0)
    {
        $settings = new \vc\object\Settings();
        $settings->profileid = $profileId;

        $settings->values[\vc\object\Settings::DESIGN] = '';
        $settings->values[\vc\object\Settings::NEW_MAIL_NOTIFICATION] = 1;
        $settings->values[\vc\object\Settings::NEW_FRIEND_NOTIFICATION] = 1;
        $settings->values[\vc\object\Settings::FRIEND_CHANGED_NOTIFICATION] = 0;
        $settings->values[\vc\object\Settings::GROUP_MEMBER_NOTIFICATION] = 1;
        $settings->values[\vc\object\Settings::USER_LANGUAGE] = 'en';

        $settings->values[\vc\object\Settings::SAVEDSEARCH_DISPLAY] = \vc\object\Settings::SAVEDSEARCH_DISPLAY_INFOBOX;
        $settings->values[\vc\object\Settings::SAVEDSEARCH_COUNT] = 6;

        $settings->values[\vc\object\Settings::DISTANCE_UNIT] = \vc\object\Settings::DISTANCE_UNIT_KILOMETER;
        $settings->values[\vc\object\Settings::PROFILE_WATERMARK] = 1;
        $settings->values[\vc\object\Settings::ROTATE_PICS] = 1;
        $settings->values[\vc\object\Settings::SEARCHENGINE] = 1;
        $settings->values[\vc\object\Settings::VISIBLE_ONLINE] = 1;
        $settings->values[\vc\object\Settings::VISIBLE_LAST_VISITOR] = 1;
        $settings->values[\vc\object\Settings::PLUS_MARKER] = 1;
//        $settings->values[\vc\object\Settings::AUTO_EXPAND_PROFILES] = 0;
        $settings->values[\vc\object\Settings::AGE_RANGE_FILTER] = 0;
        $settings->values[\vc\object\Settings::PM_FILTER_INCOMING] = 1;
        $settings->values[\vc\object\Settings::PM_FILTER_OUTGOING] = 1;
        $settings->values[\vc\object\Settings::TRACKING] = 1;
        $settings->values[\vc\object\Settings::PRESS_INTERVIEW_PARTNER] = 0;
        $settings->values[\vc\object\Settings::BETA_USER] = 0;

        if (!empty($profileId)) {
            $query = 'SELECT field, value FROM vc_setting WHERE profileid = ?';
            $statement = $this->getDb()->queryPrepared($query, array(intval($profileId)));
            $statement->bind_result(
                $field,
                $value
            );
            while ($statement->fetch()) {
                $settings->values[$field] = $value;
            }
            $statement->close();
        }
        return $settings;
    }

    public function setBooleanValue($profileId, $field, $value)
    {
        if (empty($value) || $value == 0) {
            return $this->setStringValue($profileId, $field, '0');
        } else {
            return $this->setStringValue($profileId, $field, '1');
        }
    }

    public function setStringValue($profileId, $field, $value)
    {
        $this->values[$field] = $value;
        return $this->getDb()->executePrepared(
            'INSERT INTO vc_setting SET profileid = ?, field = ?, value = ? ON DUPLICATE KEY UPDATE value = ?',
            array(
                intval($profileId),
                intval($field),
                $value,
                $value
            )
        );
    }
}
