<?php
namespace vc\controller\web;

class PictureController extends \vc\controller\web\AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if ($this->site == 'user/picture') {
             $picRootDir = PROFILE_PIC_DIR;
        } elseif ($this->site === 'groups/picture') {
            $picRootDir = GROUP_PIC_DIR;
        } elseif ($this->site === 'forum/thread/picture') {
            $picRootDir = THREAD_PIC_DIR;
        } elseif ($this->site === 'picture/temp') {
            $picRootDir = TEMP_PIC_DIR;
        } elseif ($this->site === 'events/picture') {
            $picRootDir = EVENT_PIC_DIR;
        } elseif ($this->site === 'mod/real/picture') {
            if (!$this->getSession()->isAdmin()) {
                header('HTTP/1.0 404 Not Found');
                return;
            }
            $picRootDir = REAL_PIC_DIR;
        } else {
            \vc\lib\ErrorHandler::error(
                'Wrong site for pictureController: ' . $this->site,
                __FILE__,
                __LINE__
            );
            return;
        }

        $siteparams = $this->siteParams;
        if (count($siteparams) > 3 && $siteparams[0] === 'crop') {
            // Cropping the image
            try {
                $width = intval($siteparams[1]);
                $height = intval($siteparams[2]);
                $filename = $siteparams[3];
                $subDir = $width . 'x' . $height;

                if (!$this->isPathAllowed($subDir)) {
                    $this->addSuspicion(
                        \vc\model\db\SuspicionDbModel::TYPE_PATH_MANIPULATION,
                        array(
                            'siteParams' => $this->siteParams
                        )
                    );

                    if ($this->getServer() == 'local') {
                        \vc\lib\ErrorHandler::notice(
                            'Invalid subDir ' . $subDir,
                            __FILE__,
                            __LINE__
                        );
                    }
                    header('HTTP/1.0 404 Not Found');
                    return;
                }

                $directory = $picRootDir . '/' . $subDir;
                if (!file_exists($directory)) {
                    $created = mkdir($directory);
                    if (!$created) {
                        \vc\lib\ErrorHandler::error(
                            'Can\'t create directory ' . $directory,
                            __FILE__,
                            __LINE__
                        );
                        return;
                    }
                }

                // Exit if filename is invalid
                if (!preg_match('/^[a-z0-9]+(.jpg)$/ix', $filename)) {
                    header('HTTP/1.0 404 Not Found');
                    return;
                }

                $file = $directory . '/' . $filename;
                // Create picture on demand
                if (!file_exists($file)) {
                    $pictureSaveComponent = $this->getComponent('PictureSave');
                    $pictureSaveComponent->createCropPicture($picRootDir, $filename, $file, $width, $height);
                }
                // Exit if file is not readable (Should NEVER happen)
                if (!is_readable($file)) {
                    if ($this->getServer() == 'local') {
                        \vc\lib\ErrorHandler::notice(
                            'File ' . $file . ' is not readable.',
                            __FILE__,
                            __LINE__
                        );
                    }
                    header('HTTP/1.0 404 Not Found');
                } else {
                    $this->outputImageToBrowser($file);
                }
            } catch (\vc\exception\NotFoundException $exception) {
                if ($this->getServer() == 'local') {
                    \vc\lib\ErrorHandler::notice(
                        'SourcePicture not found.',
                        __FILE__,
                        __LINE__,
                        array(
                            'siteparams' => $siteparams
                        )
                    );
                }
                header('HTTP/1.0 404 Not Found');
            }
        } elseif (count($siteparams) > 2 && $siteparams[0] === 'w') {
            // Limit to width
            $width = intval($siteparams[1]);
            // Make sure that nobody uses this script to get to a more sensitive path
            $filename = preg_replace('@([^a-z,0-9,\.])@', '', $siteparams[2]);

            if ($width > 0) {
                $subDir = 'w' . $width;

                if (!$this->isPathAllowed($subDir)) {
                        \vc\lib\ErrorHandler::notice(
                            'Invalid subDir ' . $subDir,
                            __FILE__,
                            __LINE__
                        );
                    if ($this->getServer() == 'local') {
                        header('HTTP/1.0 404 Not Found');
                        return;
                    }
                }

                $directory = $picRootDir . '/w' . $width;
                if (!file_exists($directory)) {
                    $created = mkdir($directory);
                    if (!$created) {
                        \vc\lib\ErrorHandler::error(
                            'Can\'t create directory ' . $directory,
                            __FILE__,
                            __LINE__
                        );
                        return;
                    }
                }

                $imageFile = $directory . '/' . $filename;
                if (!file_exists($imageFile)) {
                    $fullSource = $this->getFullSource($picRootDir, $filename);
                    if ($fullSource !== null) {
                        $pictureSaveComponent = $this->getComponent('PictureSave');
                        $size = $pictureSaveComponent->getRescaleSize($fullSource, $width, floor($width * 0.67));
                        $pictureSaveComponent->rescale(
                            $fullSource,
                            $directory,
                            $filename,
                            $size[0],
                            $size[1]
                        );
                    }
                }

                if (file_exists($imageFile) && is_readable($imageFile)) {
                    $this->outputImageToBrowser($imageFile);
                } else {
                    header('HTTP/1.0 404 Not Found');
                    if ($this->getServer() == 'local') {
                        \vc\lib\ErrorHandler::notice(
                            'File ' . $subDir . '/' . $filename . ' is not available ' .
                            '(' . file_exists($imageFile) . '/' . is_readable($imageFile) . ') .',
                            __FILE__,
                            __LINE__
                        );
                    }
                }
            } else {
                header('HTTP/1.0 404 Not Found');
            }
        } elseif (count($siteparams) > 1) {
            if ($siteparams[0] === 'full') {
                if ($this->site == 'user/picture') {
                    $pictureModel = $this->getDbModel('Picture');
                    $profileId = $pictureModel->getField('profileid', 'filename', $siteparams[1]);
                    if (empty($profileId)) {
                        // Entry not found in database
                        header('HTTP/1.0 404 Not Found');
                        return;
                    } else {
                        $cacheModel = $this->getModel('Cache');
                        $settings = $cacheModel->getSettings($profileId);
                        if (empty($settings)) {
                            \vc\lib\ErrorHandler::error(
                                'Can\'t find profile settings for profile ' . $profileId,
                                __FILE__,
                                __LINE__
                            );
                            $subDir = 'full-watermark';
                        } else {
                            if ($settings->getValue(\vc\object\Settings::PROFILE_WATERMARK, true)) {
                                $subDir = 'full-watermark';
                            } else {
                                $subDir = 'full';
                            }
                        }
                    }
                } else if ($this->site === 'mod/real/picture') {
                    $subDir = 'full';
                } else {
                    $subDir = 'full-watermark';
                }
            } else {
                $subDir = $siteparams[0];
            }
            $filename = $siteparams[1];
            if ($subDir === 'full-watermark' ||
                $subDir === 'full' ||
                $subDir === 'small') {
                // Make sure that nobody uses this script to get to a more sensitive path
                $filename = preg_replace('@([^a-z,0-9,\.])@', '', $filename);

                $directory = $picRootDir . '/' . $subDir;
                if (!file_exists($directory)) {
                    $created = mkdir($directory);
                    if (!$created) {
                        \vc\lib\ErrorHandler::error(
                            'Can\'t create directory ' . $directory,
                            __FILE__,
                            __LINE__
                        );
                        return;
                    }
                }

                $imageFile = $directory . '/' . $filename;

                if (!file_exists($imageFile)) {
                    $fullSource = $this->getFullSource($picRootDir, $filename);
                    if ($fullSource !== null) {
                        // Rescale the picture
                        $pictureSaveComponent = $this->getComponent('PictureSave');
                        if ($subDir === 'full-watermark') {
                            $pictureSaveComponent->addWatermarkToFile($fullSource, $imageFile);
                        } elseif ($subDir === 'small') {
                            $size = $pictureSaveComponent->getRescaleSize($fullSource, 125, 154);
                            $pictureSaveComponent->rescale(
                                $fullSource,
                                $picRootDir . '/small',
                                $filename,
                                $size[0],
                                $size[1]
                            );
                        }
                    }
                }

                if (file_exists($imageFile) && is_readable($imageFile)) {
                    $this->outputImageToBrowser($imageFile);
                } else {
                    header('HTTP/1.0 404 Not Found');
                    if ($this->getServer() == 'local') {
                        \vc\lib\ErrorHandler::notice(
                            'File ' . $subDir . '/' . $filename . ' is not available ' .
                            '(' . file_exists($imageFile) . '/' . is_readable($imageFile) . ') .',
                            __FILE__,
                            __LINE__
                        );
                    }
                }
            } else {
                header('HTTP/1.0 404 Not Found');
            }
        } else {
            header('HTTP/1.0 404 Not Found');
        }
    }


    private function getFullSource($picRootDir, $filename)
    {
        // Select the correct source file
        $fullClearFile = $picRootDir . '/full/' . $filename;
        if (file_exists($fullClearFile) && is_readable($fullClearFile)) {
            $fullSource = $fullClearFile;
        } else {
            if ($this->getServer() == 'local') {
                \vc\lib\ErrorHandler::notice(
                    'The full-source for ' . $filename . ' is not available ' .
                    '(' . file_exists($fullClearFile) . '/' . is_readable($fullClearFile) . ') .',
                    __FILE__,
                    __LINE__
                );
            }
            return null;
        }
        return $fullSource;
    }

    private function outputImageToBrowser($file)
    {
        header('Content-Type: image/jpg', true);
        header('Content-Disposition: inline;', true);
        header('Expires: Wed, 01 Jan 2025 01:00:00 GMT', true);
        header('Last-Modified: Wed, 01 Jan 2015 01:00:00 GMT', true);
        header('X-Robots-Tag: noindex', true);
        header('Cache-Control:max-age=31536000', true);
        header_remove('Pragma');
        readfile($file);
    }

    private function isPathAllowed($path)
    {
        return $path === '74x74' ||
               $path === '100x100' ||
               $path === '200x200' ||
               $path === 'w390';
    }
}
