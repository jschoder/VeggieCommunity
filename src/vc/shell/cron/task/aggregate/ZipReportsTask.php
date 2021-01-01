<?php
namespace vc\shell\cron\task\aggregate;

class ZipReportsTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $today = date('Y-m-d');

        $reportDirHandle = opendir(APP_REPORTS);
        foreach (scandir(APP_REPORTS) as $file) {
            $path = APP_REPORTS . '/' . $file;
            if (is_dir($path) && $file !== '.' && $file !== '..') {
                if ($this->isTestMode()) {
                    echo "\n" . $file ;
                    if ($file === $today) {
                        echo ' (today)';
                    } else {
                        echo ' (archive)';
                    }
                }

                if ($file !== $today) {
                    $zipFilename = $file . '.zip';
                    for ($i=2; file_exists(APP_REPORTS . '/' . $zipFilename); $i++) {
                        $zipFilename = $file . '_' . $i .'.zip';
                    }

                    $archiveFiles = array_diff(scandir(APP_REPORTS . '/' . $file), array('..', '.'));

                    if ($this->isTestMode()) {
                        echo ' -> ' . $zipFilename;
                        echo "\n    " . implode("\n    ", $archiveFiles);
                    } else {
                        // Creating the zip
                        $archive = new \ZipArchive();
                        if ($archive->open(APP_REPORTS . '/' . $zipFilename, \ZipArchive::CREATE)) {
                            foreach ($archiveFiles as $archiveFile) {
                                $archive->addFile(APP_REPORTS . '/' . $file . '/' . $archiveFile);
                            }
                            $archive->close();
                            chmod(APP_REPORTS . '/' . $zipFilename, 16895); // 0777
                        }

                        // Cleaning up the old directory
                        foreach ($archiveFiles as $archiveFile) {
                            unlink(APP_REPORTS . '/' . $file . '/' . $archiveFile);
                        }
                        rmdir(APP_REPORTS . '/' . $file);
                    }
                }
            }
        }
    }
}
