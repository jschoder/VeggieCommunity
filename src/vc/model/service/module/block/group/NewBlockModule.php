<?php
namespace vc\model\service\module\block\group;

class NewBlockModule extends \vc\model\service\module\block\AbstractBlockModule
{
    protected function render($path, $imagesPath)
    {
        $groupModel = $this->getDbModel('Group');
        $groups = $groupModel->loadObjects(
            array(
                'deleted_at IS NULL',
                'confirmed_by IS NOT NULL'
            ),
            array(),
            'id DESC',
            12
        );

        return $this->element(
            'block/group',
            array(
                'path' => $path,
                'imagesPath' => $imagesPath,
                'id' => 'blockGroupNew',
                'title' => gettext('block.group.new.title'),
                'groups' => $groups
            )
        );
    }
}
