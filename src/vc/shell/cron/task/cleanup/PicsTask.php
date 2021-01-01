<?php
namespace vc\shell\cron\task\cleanup;

class PicsTask extends \vc\shell\cron\task\AbstractCronTask
{
    private $skippedFiles = array();

    private $directoryLog = array();

    public function execute()
    {
        $query = 'SELECT count(*) FROM vc_picture';
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('picCountBefore', $row[0]);


        $queryPostfix = 'WHERE profileid IN ' .
                        '(SELECT id FROM vc_profile WHERE  ' .
                        '(delete_date IS NULL OR delete_date < DATE_SUB(NOW(), INTERVAL 14 DAY)) AND ' .
                        'active < 0)';
        $query = 'SELECT count(*) FROM vc_picture ' . $queryPostfix;
        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $this->setDebugInfo('deletedProfilesPictures', $row[0]);

        $query = 'DELETE FROM vc_picture ' . $queryPostfix;
        if (!$this->isTestMode()) {
            // :TODO: JOE - deprecated
            $this->getDb()->delete($query);
        }

        $query = 'SELECT filename FROM vc_picture';
        $result = $this->getDb()->select($query);
        $dbPics = array();
        for ($i=0; $i < $result->num_rows; $i++) {
            $row = $result->fetch_row();
            $dbPics[] = $row[0];
        }
        $this->setDebugInfo('picturesInDatabase', count($dbPics));

        if (count($dbPics) > 0) {
            $cleared_size = 0;
            $cleared_size += $this->clearDirectory($dbPics, 'full-watermark');
            $cleared_size += $this->clearDirectory($dbPics, 'full');
            $cleared_size += $this->clearDirectory($dbPics, 'small');
            $cleared_size += $this->clearDirectory($dbPics, '74x74');
            $cleared_size += $this->clearDirectory($dbPics, '100x100');
            $cleared_size += $this->clearDirectory($dbPics, '200x200');

            $this->setDebugInfo('totalClearedKb', round($cleared_size / pow(1024, 1), 3));
        }

        $this->setDebugInfo('skippedFiles', $this->skippedFiles);
        $this->setDebugInfo('directoryLog', $this->directoryLog);
    }

    private function clearDirectory($dbPics, $path)
    {
        $rootPath = PROFILE_PIC_DIR . '/';
        if (file_exists($rootPath . $path)) {
            $reportDirHandle = opendir($rootPath . $path);
            $filesKept = 0;
            $filesKeptSize = 0;
            $filesCleared = 0;
            $filesClearedSize = 0;

            while (false !== ($file = readdir($reportDirHandle))) {
                $filepath = $rootPath . $path . "/" . $file;
                if ($file != "." && $file != "..") {
                    if (in_array($file, $dbPics)) {
                        $filesKept++;
                        $filesKeptSize = $filesKeptSize + filesize($filepath);
                    } else {
                        $query2 = "SELECT id FROM vc_picture WHERE filename='" . $file. "' LIMIT 1";
                        $result2 = $this->getDb()->select($query2);
                        if ($result2->num_rows > 0) {
                            $this->skippedFiles[] = $path . '/' . $file;
                        } else {
                            $filesCleared++;
                            $filesClearedSize = $filesClearedSize + filesize($filepath);
                            if (!$this->isTestMode()) {
                                unlink($filepath);
                            }
                        }
                    }
                }
            }
            $this->directoryLog[$path] = array('kept' => round($filesKeptSize / pow(1024, 1), 3),
                                               'cleaned' => round($filesClearedSize / pow(1024, 1), 3));
            return $filesClearedSize;
        } else {
            return 0;
        }
    }
}
