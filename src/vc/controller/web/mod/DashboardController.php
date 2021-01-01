<?php
namespace vc\controller\web\mod;

class DashboardController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Mod Dashboard');

        $modMessageModel = $this->getDbModel('ModMessage');
        $this->getView()->set('modMessages', $modMessageModel->getRecentMessages());
        $this->getView()->set('lastErrors', $this->getLastErrors());
        $this->getView()->set('errorArchives', $this->getErrorArchives());

        $watchlistModel = $this->getDbModel('Watchlist');
        $this->getView()->set('watchlist', $watchlistModel->getList());

        echo $this->getView()->render('mod/dashboard', true);
    }

    private function getLastErrors()
    {
        $lastErrors = array();
        $errorDir = APP_REPORTS . '/' . date('Y-m-d');
        if (file_exists($errorDir)) {
            $errorLogs = array_reverse(array_diff(scandir($errorDir), array('..', '.')));

            // Removing "." and ".."
            foreach ($errorLogs as $errorLogFile) {
                if (count($lastErrors) < 25) {
                    $errorContent = explode("\n", file_get_contents($errorDir . '/' . $errorLogFile));
                    $lastErrors[] = array(
                        // Timestamp
                        strtotime(substr($errorContent[1], 6)),
                        // Error message
                        substr($errorContent[5], 5),
                        // File + Line
                        strpos($errorContent[6], 'file: ') === 0 ? substr($errorContent[6], 6) : ''
                    );
                }
            }
        }

        return $lastErrors;
    }

    private function getErrorArchives()
    {
        $errorArchives = array();
        foreach (scandir(APP_REPORTS) as $file) {
            $path = APP_REPORTS . '/' . $file;
            if (!is_dir($path) && substr($file, -4) === '.zip') {
                $archive = new \ZipArchive();
                $archive->open($path);
                $errorArchives[$file] = $archive->numFiles;
                $archive->close();
            }
        }
        return $errorArchives;
    }
}
