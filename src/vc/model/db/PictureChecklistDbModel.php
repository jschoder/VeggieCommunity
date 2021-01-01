<?php
namespace vc\model\db;

class PictureChecklistDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_picture_checklist';
    const OBJECT_CLASS = '\\vc\object\\PictureChecklist';

    public function clearDeletedPictures()
    {
        $query = 'DELETE vc_picture_checklist
                  FROM vc_picture_checklist
                  LEFT JOIN vc_picture ON vc_picture_checklist.id = vc_picture.id
                  WHERE vc_picture.id IS NULL';
        $this->getDb()->executePrepared($query);
    }
}
