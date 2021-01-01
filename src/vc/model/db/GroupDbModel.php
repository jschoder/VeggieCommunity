<?php
namespace vc\model\db;

class GroupDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_group';
    const OBJECT_CLASS = '\\vc\object\\Group';

    public function getGroupsByConfirmedMember($userId)
    {
        $query = 'SELECT hash_id, name FROM vc_group
                  INNER JOIN vc_group_member ON vc_group.id = vc_group_member.group_id
                  WHERE vc_group_member.profile_id = ? AND vc_group_member.confirmed_at IS NOT NULL
                  ORDER BY vc_group.name';
        $statement = $this->getDb()->queryPrepared($query, array(intval($userId)));
        $groups = array();
        $statement->bind_result(
            $hashId,
            $name
        );
        while ($statement->fetch()) {
            $groups[$hashId] = $name;
        }
        $statement->close();
        return $groups;
    }

    public function getUnconfirmedGroups()
    {
        $query = 'SELECT vc_group.id, image, name, description, rules, mod_message, created_at,
                         vc_profile.id, vc_profile.nickname, vc_profile.first_entry
                  FROM vc_group
                  LEFT JOIN vc_profile ON vc_profile.id = vc_group.created_by
                  WHERE confirmed_at IS NULL AND deleted_at IS NULL';
        $statement = $this->getDb()->queryPrepared($query);

        $groups = array();
        $statement->bind_result(
            $id,
            $image,
            $name,
            $description,
            $rules,
            $modMessage,
            $createdAt,
            $profileId,
            $profileNickname,
            $profileFirstEntry
        );
        while ($statement->fetch()) {
            $groups[] = array(
                'id' => $id,
                'image' => $image,
                'name' => $name,
                'description' => $description,
                'rules' => $rules,
                'modMessage' => $modMessage,
                'createdAt' => $createdAt,
                'profileId' => $profileId,
                'profileNickname' => $profileNickname,
                'profileFirstEntry' => $profileFirstEntry
            );
        }
        $statement->close();

        return $groups;
    }

    public function setGroupConfirmed($groupId, $confirmedBy)
    {
        $query = 'UPDATE vc_group SET confirmed_by = ?, confirmed_at = ? WHERE id = ? LIMIT 1';
        $statement = $this->getDb()->prepare($query);
        $now = date('Y-m-d H:i:s');
        $statement->bind_param('isi', $confirmedBy, $now, $groupId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while confirming group: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('groupId' => $groupId,
                      'confirmedBy' => $confirmedBy)
            );
            return false;
        }
        $statement->close();
        return true;
    }

    public function setGroupDenied($groupId, $deletedBy)
    {
        $query = 'UPDATE vc_group SET deleted_by = ?, deleted_at = ? WHERE id = ?';
        $statement = $this->getDb()->prepare($query);
        $now = date('Y-m-d H:i:s');
        $statement->bind_param('isi', $deletedBy, $now, $groupId);
        $executed = $statement->execute();
        if (!$executed) {
            \vc\lib\ErrorHandler::error(
                'Error while denying group: ' . $statement->errno . ' / ' . $statement->error,
                __FILE__,
                __LINE__,
                array('groupId' => $groupId,
                      'deletedBy' => $deletedBy)
            );
        }
        $statement->close();
    }

    public function searchGroup($searchPhrase, $searchSort, $offset, $limit)
    {
        $query = 'SELECT hash_id, image, name, activity, groupsize.member_count
                  FROM vc_group
                  LEFT JOIN (SELECT group_id, count(profile_id) AS member_count FROM vc_group_member
                             WHERE confirmed_at IS NOT NULL GROUP BY group_id)
                  as groupsize ON vc_group.id = groupsize.group_id
                  WHERE confirmed_at IS NOT NULL AND deleted_at IS NULL';
        $queryParams = array();
        if (!empty($searchPhrase)) {
            $searchWords = explode(' ', $searchPhrase);
            foreach ($searchWords as $searchWord) {
                $searchWord = trim($searchWord);
                if (!empty($searchWord)) {
                    $query .= ' AND (name LIKE ? OR description LIKE ?)';
                    $queryParams[] = '%' . $this->prepareSQL($searchWord) . '%';
                    $queryParams[] = '%' . $this->prepareSQL($searchWord) . '%';
                }
            }
        }

        if ($searchSort == 'active') {
            $query .= ' ORDER BY activity DESC, confirmed_at DESC, id DESC';
        } else if ($searchSort == 'members') {
            $query .= ' ORDER BY member_count DESC, confirmed_at DESC, id DESC';
        } else  {
            $query .= ' ORDER BY confirmed_at DESC, id DESC';
        }
        $query .= ' LIMIT ?, ?';
        $queryParams[] = intval($offset);
        $queryParams[] = intval($limit);

        $statement = $this->getDb()->queryPrepared($query, $queryParams);

        $groups = array();
        $statement->bind_result($hashId, $image, $name, $activity, $memberCount);
        while ($statement->fetch()) {
            $group = new \stdClass();
            $group->hashId = $hashId;
            $group->image = $image;
            $group->name = $name;
            $group->members = $memberCount;
            $group->activity = $activity;
            $groups[] = $group;
        }
        $statement->close();

        return $groups;
    }

    public function searchGroupCount($searchPhrase)
    {
        $query = 'SELECT count(id) as rowCount
                  FROM vc_group
                  WHERE confirmed_at IS NOT NULL AND deleted_at IS NULL';
        $queryParams = array();
        if (!empty($searchPhrase)) {
            $searchWords = explode(' ', $searchPhrase);
            foreach ($searchWords as $searchWord) {
                $searchWord = trim($searchWord);
                if (!empty($searchWord)) {
                    $query .= ' AND (name LIKE ? OR description LIKE ?)';
                    $queryParams[] = '%' . $this->prepareSQL($searchWord) . '%';
                    $queryParams[] = '%' . $this->prepareSQL($searchWord) . '%';
                }
            }
        }

        $statement = $this->getDb()->queryPrepared($query, $queryParams);
        $statement->bind_result($rowCount);
        $statement->fetch();
        $statement->close();

        return $rowCount;
    }

    public function getVisibleGroupsByUser($userId, $currentUserId)
    {
        $groups = array();
        if ($userId === $currentUserId) {
            $groups['shared'] = array();
        } else {
            $groups['shared'] = $this->loadObjects(
                array(
                    'vc_group_member.profile_id' => $userId,
                    'own_groups.profile_id' => $currentUserId
                ),
                array(
                    'INNER JOIN vc_group_member ON vc_group.id = vc_group_member.group_id',
                    'INNER JOIN vc_group_member own_groups ON vc_group.id = own_groups.group_id'
                ),
                'vc_group.name ASC'
            );
        }

        $publicWhere = array(
            'vc_group_member.profile_id' => $userId,
            'vc_group.member_visibility' => \vc\object\Group::MEMBER_VISBILITY_SITE_MEMBERS
        );
        if (!empty($groups['shared'])) {
            $sharedIds = array();
            foreach ($groups['shared'] as $group) {
                $sharedIds[] = $group->id;
            }
            $publicWhere['vc_group.id NOT'] = $sharedIds;
        }
        $groups['public'] = $this->loadObjects(
            $publicWhere,
            array(
                'INNER JOIN vc_group_member ON vc_group.id = vc_group_member.group_id'
            ),
            'vc_group.name ASC'
        );

        return $groups;
    }
}
