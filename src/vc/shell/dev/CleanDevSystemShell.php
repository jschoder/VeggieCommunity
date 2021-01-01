<?php
namespace vc\shell\dev;

class CleanDevSystemShell extends \vc\shell\AbstractShell
{
    public function run()
    {
        $this->removeMissingPicsFromDatabase();
    }

    private function removeMissingPicsFromDatabase()
    {
        echo "Loading pics ...\n";
        $query = 'SELECT id, filename FROM vc_picture';
        $result = $this->getDb()->select($query);
        $dbPics = array();
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $dbPics[$row[1]] = $row[0];
        }
        echo "Done (" . count($dbPics) . ") \n";



        echo "Parse pictures ...\n";
        $reportDirHandle = opendir(APP_ROOT . '/pictures/full');
        while (false !== ($file = readdir($reportDirHandle))) {
            if (array_key_exists($file, $dbPics)) {
                unset($dbPics[$file]);
            }
        }
        echo "Done (" . count($dbPics) . ") \n";



        echo "Deleting pictures ...\n";
        foreach ($dbPics as $fileName => $id) {
            $this->getDb()->executePrepared(
                'DELETE FROM vc_picture WHERE id = ?',
                array(intval($id))
            );
        }
        echo "Done \n";
    }
}
