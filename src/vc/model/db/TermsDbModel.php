<?php
namespace vc\model\db;

class TermsDbModel extends AbstractDbModel
{
    public function areAllTermsConfirmed($profileid)
    {
        $query = '(SELECT terms_id ' .
                 'FROM vc_termsofuse_confirm ' .
                 'WHERE profile_id=? AND ' .
                 '(terms_id IN (' .
                 'SELECT id FROM vc_termsofuse ' .
                 'WHERE type=? AND locale="de" AND ' .
                 'id >= (SELECT id FROM vc_termsofuse ' .
                 'WHERE confirmation_necessary = 1 AND ' .
                 'type = ? AND locale="de" ' .
                 'ORDER BY id DESC LIMIT 1)) OR  ' .
                 'terms_id IN (' .
                 'SELECT id FROM vc_termsofuse ' .
                 'WHERE type=? AND locale="en" AND ' .
                 'id >= (SELECT id FROM vc_termsofuse ' .
                 'WHERE confirmation_necessary = 1 AND ' .
                 'type = ? AND locale="en" ' .
                 'ORDER BY id DESC LIMIT 1))) ' .
                 'LIMIT 1)';
        $queryParams = array(
            intval($profileid),
            \vc\object\Terms::TYPE_TERMS_OF_USE,
            \vc\object\Terms::TYPE_TERMS_OF_USE,
            \vc\object\Terms::TYPE_TERMS_OF_USE,
            \vc\object\Terms::TYPE_TERMS_OF_USE,
            intval($profileid),
            \vc\object\Terms::TYPE_PRIVACY_POLICY,
            \vc\object\Terms::TYPE_PRIVACY_POLICY,
            \vc\object\Terms::TYPE_PRIVACY_POLICY,
            \vc\object\Terms::TYPE_PRIVACY_POLICY
        );

        $statement = $this->getDb()->queryPrepared(
            $query . ' UNION ' . $query,
            $queryParams
        );

        $statement->store_result();
        $confirmed = ($statement->num_rows === 2);
        $statement->close();

        return $confirmed;
    }

    public function getLatestVersion($type, $locale)
    {
        $query = sprintf(
            'SELECT id, content FROM vc_termsofuse WHERE ' .
            'type = %d AND locale = \'%s\' ORDER BY version DESC',
            intval($type),
            $locale
        );
        $result = $this->getDb()->select($query);
        if ($result->num_rows === 0) {
            $result->free();
            return null;
        } else {
            $row = $result->fetch_row();
            $terms = new \vc\object\Terms();
            $terms->id = $row[0];
            $terms->content = $row[1];
            $result->free();
            return $terms;
        }
    }

    public function getChanges($locale, $profileId)
    {
        $subQuery = 'SELECT changes  FROM vc_termsofuse
                     WHERE type = ? AND locale = ? AND id > (
                     SELECT terms_id FROM vc_termsofuse_confirm
                     WHERE profile_id = ? AND
                     terms_id IN (SELECT id FROM vc_termsofuse WHERE type = ?)
                     ORDER BY terms_id DESC LIMIT 1)';
        $query = $subQuery . ' UNION ' . $subQuery;

        $statement = $this->getDb()->queryPrepared(
            $query,
            array(
                \vc\object\Terms::TYPE_TERMS_OF_USE,
                $locale,
                intval($profileId),
                \vc\object\Terms::TYPE_TERMS_OF_USE,
                \vc\object\Terms::TYPE_PRIVACY_POLICY,
                $locale,
                intval($profileId),
                \vc\object\Terms::TYPE_PRIVACY_POLICY
            )
        );
        $statement->bind_result($changesText);
        $changes = array();
        while ($statement->fetch()) {
            $changesText = trim($changesText);
            if (!empty($changesText)) {
                foreach (explode("\n", $changesText) as $changeLine) {
                    $changes[] = trim($changeLine);
                }
            }
        }
        $statement->close();

        return $changes;
    }

    public function saveTerms($type, $termId, $profileId, $ip)
    {
        $now = date('Y-m-d H:i:s');
        return $this->getDb()->executePrepared(
            'INSERT INTO vc_termsofuse_confirm (terms_id, profile_id, confirmation_date, ip)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE confirmation_date = ?, ip = ?',
            array(
                intval($termId),
                intval($profileId),
                $now,
                $ip,
                $now,
                $ip
            )
        );
    }
}
