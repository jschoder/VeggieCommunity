<?php
namespace vc\model\db;

class HobbyDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_hobby';
    const OBJECT_CLASS = '\\vc\object\\Hobby';

    public function getIndexedHobbies($locale)
    {
        $nameVariable = 'name' . ucfirst($locale);

        $hobbyGroupModel = $this->getDbModel('HobbyGroup');
        $hobbiesGroups = $hobbyGroupModel->loadObjects(array(), array(), 'name_' . $locale);
        $hobbies = $this->loadObjects(array(), array(), 'name_' . $locale);

        $groups = array();
        $otherGroup = null;
        foreach ($hobbiesGroups as $group) {
            if ($group->id === 100) {
                $otherGroup = $group->$nameVariable;
            } else {
                $groups[$group->id] = array(
                    'title' => $group->$nameVariable,
                    'hobbies' => array()
                );
            }
        }
        if ($otherGroup !== null) {
            $groups[100] = array(
                'title' => $otherGroup,
                'hobbies' => array()
            );
        }

        foreach ($hobbies as $hobby) {
            if (array_key_exists($hobby->groupId, $groups)) {
                $groups[$hobby->groupId]['hobbies'][$hobby->id] = $hobby->$nameVariable;
            }
        }

        return $groups;
    }
}
