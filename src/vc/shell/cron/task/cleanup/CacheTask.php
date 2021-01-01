<?php
namespace vc\shell\cron\task\cleanup;

class CacheTask extends \vc\shell\cron\task\AbstractCronTask
{
    // :TODO: JOE - remove deprecated cache entries

    public function execute()
    {
        $this->cleanJsCssCache();
        $this->cleanCache('forms', \vc\model\CacheModel::$expires[\vc\model\CacheModel::FORMS]);
        $this->cleanCache('hobbies', \vc\model\CacheModel::$expires[\vc\model\CacheModel::HOBBIES]);
        $this->cleanCache('module', 86400); // Maximum expiration of a module
        $this->cleanCache('pages', \vc\model\CacheModel::$expires[\vc\model\CacheModel::PAGE]);
        $this->cleanProfileCache(86400); // Maximum expiration of simple file
    }

    private function cleanJsCssCache()
    {
        if ($this->isTestMode()) {
            echo "\nCleanup Js/Css-Cache\n";
        }
        $deleted = 0;
        $kept = 0;
        if (file_exists(CACHE_DIR . '/js_css/')) {
            $dirHandle = opendir(CACHE_DIR . '/js_css/');
            if ($dirHandle) {
                while (false !== ($file = readdir($dirHandle))) {
                    if ($file !== '.' && $file != '..') {
                        // Keep only the current js/css-files
                        if (strpos($file, 'js-' . \vc\config\Globals::VERSION . '-') !== 0 &&
                            strpos($file, 'css-' . \vc\config\Globals::VERSION . '-') !== 0) {
                            if ($this->isTestMode()) {
                                echo '   ' . $file . " :: delete \n";
                            } else {
                                unlink(CACHE_DIR . '/js_css/' . $file);
                            }
                            $deleted++;
                        } else {
                            if ($this->isTestMode()) {
                                echo '   ' . $file . " :: keep\n";
                            }
                            $kept++;
                        }
                    }
                }
                closedir($dirHandle);
            }
        }

        $this->setDebugInfo('jsCss.deleted', $deleted);
        $this->setDebugInfo('jsCss.kept', $kept);
    }

    private function cleanCache($key, $expires)
    {
        $path = $key . '/';

        if (file_exists(CACHE_DIR . '/' . $path)) {
            if ($this->isTestMode()) {
                echo "\nCleanup Cache in directory " . $path . "\n";
                echo '   [' . date('Y-m-d H:i:s') . "]\n";
            }

            $deleted = 0;
            $kept = 0;

            if (file_exists(CACHE_DIR . '/' . $path)) {
                $dirHandle = opendir(CACHE_DIR . '/' . $path);
                if ($dirHandle) {
                    while (false !== ($file = readdir($dirHandle))) {
                        if ($file !== '.' && $file != '..') {
                            $cacheFile = CACHE_DIR . '/' . $path . $file;
                            $lastModification = filemtime($cacheFile);
                            if ($lastModification + $expires < time()) {
                                if ($this->isTestMode()) {
                                    echo '   ' .$cacheFile . '    ' .  $file . " :: delete \n";
                                    echo '      [' . date('Y-m-d H:i:s', $lastModification + $expires) . "]\n";
                                } else {
                                    unlink(CACHE_DIR . '/' . $path . $file);
                                }
                                $deleted++;
                            } else {
                                if ($this->isTestMode()) {
                                    echo '   ' . $file . " :: keep\n";
                                    echo '      [' . date('Y-m-d H:i:s', $lastModification + $expires) . "]\n";
                                }
                                $kept++;
                            }
                        }
                    }
                    closedir($dirHandle);
                }
            }

            $this->setDebugInfo($key . '.deleted', $deleted);
            $this->setDebugInfo($key . '.kept', $kept);
        }
    }

    public function cleanProfileCache($expires)
    {
        if ($this->isTestMode()) {
            echo "\nCleanup Profile Cache \n";
            echo '   [' . date('Y-m-d H:i:s') . "]\n";
        }
        $deleted = 0;
        $kept = 0;
        if (file_exists(CACHE_DIR . '/profile/')) {
            $dirHandle = opendir(CACHE_DIR . '/profile/');
            if ($dirHandle) {
                while (false !== ($userId = readdir($dirHandle))) {
                    $keptUserFile = false;

                    if ($userId !== '.' && $userId != '..') {
                        $userDirHandle = opendir(CACHE_DIR . '/profile/' . $userId . '/');
                        while (false !== ($userFile = readdir($userDirHandle))) {
                            if ($userFile !== '.' && $userFile != '..') {
                                if (is_dir(CACHE_DIR . '/profile/' . $userId . '/' . $userFile)) {
                                    $keptSubUserFile = false;
                                    $userDirSubHandle = opendir(CACHE_DIR . '/profile/' . $userId . '/' . $userFile);
                                    while (false !== ($userSubFile = readdir($userDirSubHandle))) {
                                        if ($userSubFile !== '.' && $userSubFile != '..') {
                                            $path = $userId . '/' . $userFile . '/' . $userSubFile;
                                            $lastModification = filemtime(CACHE_DIR . '/profile/' . $path);
                                            if ($lastModification + $expires < time()) {
                                                if ($this->isTestMode()) {
                                                    echo '      ' . $path . " :: delete \n";
                                                    echo '      [' .
                                                         date('Y-m-d H:i:s', $lastModification + $expires) .
                                                         "]\n";
                                                } else {
                                                    unlink(CACHE_DIR . '/profile/' . $path);
                                                }
                                                $deleted++;
                                            } else {
                                                if ($this->isTestMode()) {
                                                    echo '      ' . $path . " :: keep\n";
                                                    echo '      [' .
                                                         date('Y-m-d H:i:s', $lastModification + $expires) .
                                                         "]\n";
                                                }
                                                $kept++;
                                                $keptUserFile = true;
                                                $keptSubUserFile = true;
                                            }
                                        }
                                    }
                                    closedir($userDirSubHandle);

                                    if ($keptSubUserFile) {
                                        if ($this->isTestMode()) {
                                            echo '   ' . $userId . "/" . $userFile .
                                                 " :: keep non-empty sub user directory \n";
                                        }
                                    } else {
                                        if ($this->isTestMode()) {
                                            echo '   ' . $userId . "/" . $userFile .
                                                 " :: remove empty sub user directory \n";
                                        } else {
                                            rmdir(CACHE_DIR . '/profile/' . $userId . '/' . $userFile);
                                        }
                                    }
                                } else {
                                    $lastModification = filemtime(CACHE_DIR . '/profile/' . $userId . '/' . $userFile);
                                    if ($lastModification + $expires < time()) {
                                        if ($this->isTestMode()) {
                                            echo '   ' . $userId . "/" . $userFile . " :: delete \n";
                                            echo '      [' . date('Y-m-d H:i:s', $lastModification + $expires) . "]\n";
                                        } else {
                                            unlink(CACHE_DIR . '/profile/' . $userId . '/' . $userFile);
                                        }
                                        $deleted++;
                                    } else {
                                        if ($this->isTestMode()) {
                                            echo '   ' . $userId . "/" . $userFile . " :: keep\n";
                                            echo '      [' . date('Y-m-d H:i:s', $lastModification + $expires) . "]\n";
                                        }
                                        $kept++;
                                        $keptUserFile = true;
                                    }
                                }
                            }
                        }
                        closedir($userDirHandle);

                        if ($keptUserFile) {
                            if ($this->isTestMode()) {
                                echo '   ' . $userId . " :: keep non-empty user directory \n";
                            }
                        } else {
                            if ($this->isTestMode()) {
                                echo '   ' . $userId . " :: remove empty user directory \n";
                            } else {
                                rmdir(CACHE_DIR . '/profile/' . $userId . '/');
                            }
                        }
                    }
                }
                closedir($dirHandle);
            }
        }

        $this->setDebugInfo('profile.deleted', $deleted);
        $this->setDebugInfo('profile.kept', $kept);
    }
}
