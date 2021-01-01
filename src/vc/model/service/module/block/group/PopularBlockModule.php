<?php
namespace vc\model\service\module\block\group;

class PopularBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    protected function render($path, $imagesPath)
    {
        $groupModel = $this->getDbModel('Group');
        $groups = $groupModel->loadObjects(
            array('deleted_at IS NULL'),
            array(
                'INNER JOIN (SELECT group_id, count(profile_id) as amount FROM vc_group_member ' .
                'GROUP BY group_id) AS member_count ON member_count.group_id = vc_group.id'
            ),
            'member_count.amount DESC',
            12
        );

        return $this->element(
            'block/group',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'id' => 'blockGroupPopular',
                'title' => gettext('block.group.popular.title'),
                'groups' => $groups
            )
        );
    }
}
