<?php
namespace vc\component;

class PictureSaveComponent extends AbstractComponent
{
    const WATERMARK_RELATION = 0.3;

    public function saveObjectPictures(array $fieldDefinition, $object, $rotatePics = true)
    {
        foreach ($fieldDefinition as $fieldName => $fieldConfig) {
            if ($fieldConfig['type'] == 'image' &&
                isset($object->$fieldName) &&
                is_array($object->$fieldName)) {
                $fileinfo = $this->saveFormPicture(
                    $object->$fieldName,
                    $fieldConfig['uploadpath'],
                    $fieldConfig['namelength'],
                    $fieldConfig['width'],
                    $fieldConfig['height'],
                    $rotatePics
                );
                if ($fileinfo === false) {
                    $object->$fieldName = null;
                } else {
                    $object->$fieldName = $fileinfo['filename'];
                }
            }
        }
    }

    public function saveFormPicture($field, $uploadpath, $namelength, $width, $height, $rotatePics = true)
    {
        if (is_uploaded_file($field['tmp_name'])) {
            do {
                $filename = '';
                $chars = array(
                                'a','b','c','d','e','f','g','h','i','j','k','l','m',
                                'n', 'o','p','q','r','s','t','u','v','w','x','y','z',
                                '1','2','3','4','5','6','7','8','9','0');
                for ($i=0; $i<$namelength; $i++) {
                    $filename .= $chars[rand(0, count($chars) - 1)];
                }
                $filename .= '.jpg';
            } while (file_exists(APP_ROOT . $uploadpath . '/' . $filename));
            $movedPic = $this->movePic(
                $field['tmp_name'],
                APP_ROOT . $uploadpath,
                $rotatePics,
                true,
                $width,
                $height
            );
            return $movedPic;
        } else {
            return false;
        }
    }

    public function movePic($sourceFile, $targetDir, $rotatePics = true, $rescale = false, $maxWidth = null, $maxHeight = null)
    {
        do {
            $filename = '';
            $chars = array(
                            'a','b','c','d','e','f','g','h','i','j','k','l','m',
                            'n', 'o','p','q','r','s','t','u','v','w','x','y','z',
                            '1','2','3','4','5','6','7','8','9','0');
            for ($i=0; $i<20; $i++) {
                $filename .= $chars[rand(0, count($chars) - 1)];
            }
            $filename .= '.jpg';
        } while (file_exists($targetDir . '/' . $filename));

        if (file_exists($sourceFile)) {
            $imageSize =  GetImageSize($sourceFile);
            // Not a valid image. Cancel the process.
            if ($imageSize === false) {
                return false;
            }

            if ($rotatePics && $imageSize[2] ===  IMAGETYPE_JPEG) {
                $exif = @exif_read_data($sourceFile, 'IFD0');
                if (is_array($exif) && !empty($exif['Orientation'])) {
                    $exifOrientation = $exif['Orientation'];
                } else {
                    $exifOrientation = 0;
                }
            } else {
                $exifOrientation = 0;
            }

            if ($rescale) {
                $size = $this->getRescaleSize($sourceFile, $maxWidth, $maxHeight, $exifOrientation);
                $width = $size[0];
                $height = $size[1];

                $rescaled = $this->rescale($sourceFile, $targetDir, $filename, $width, $height, $exifOrientation);
                if ($rescaled === false) {
                    return false;
                }
            } else {
                rename($sourceFile, $targetDir . $filename);

                $width = $imageSize[0];
                $height = $imageSize[1];
            }

            return array('filename' => $filename,
                         'width' => $width,
                         'height' => $height,
                         'exifOrientation' => $exifOrientation);
        } else {
            \vc\lib\ErrorHandler::error("Picture '" . $sourceFile . "' not found.", __FILE__, __LINE__);
            return false;
        }
    }

    public function createCropPicture(
        $picRootDir,
        $filename,
        $targetFile,
        $thumbnailWidth,
        $thumbnailHeight,
        $pic_position = 'center'
    ) {
        $sourceFile = $picRootDir . '/full/' . $filename;
        if (!file_exists($sourceFile)) {
            $sourceFile = $picRootDir . '/full-watermark/' . $filename;
        }

        // If neither file exists it should be handled as such
        if (!file_exists($sourceFile)) {
//           \vc\lib\ErrorHandler::notice('No sourcefile for ' . $filename, __FILE__, __LINE__);
            throw new \vc\exception\NotFoundException();
        }

        list($width_orig, $height_orig) = getimagesize($sourceFile);
        $myImage = imagecreatefromjpeg($sourceFile);
        $ratio_orig = $width_orig/$height_orig;

        if ($thumbnailWidth/$thumbnailHeight > $ratio_orig) {
            $new_height = $thumbnailWidth/$ratio_orig;
            $new_width = $thumbnailWidth;
        } else {
            $new_width = $thumbnailHeight*$ratio_orig;
            $new_height = $thumbnailHeight;
        }

        $x_mid = $new_width / 2;  //horizontal middle
        $y_mid = $new_height / 2; //vertical middle
        if ($pic_position == 'top') {
            $start_x = $x_mid - ($thumbnailWidth / 2);
            $start_y = 0;
        } elseif ($pic_position == 'bottom') {
            $start_x = $x_mid - ($thumbnailWidth / 2);
            $start_y = $new_height - $thumbnailHeight;
        } else { // if ($pic_position == 'center')
            $start_x = $x_mid - ($thumbnailWidth / 2);
            $start_y = $y_mid - ($thumbnailHeight / 2);
        }

        $process = imagecreatetruecolor(round($new_width), round($new_height));

        imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $thumb = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
        imagecopyresampled(
            $thumb,
            $process,
            0,
            0,
            $start_x,
            $start_y,
            $thumbnailWidth,
            $thumbnailHeight,
            $thumbnailWidth,
            $thumbnailHeight
        );

        imagedestroy($process);
        imagedestroy($myImage);
        imagejpeg($thumb, $targetFile);
        imagedestroy($thumb);
    }

    //--------------------------------------------------------------------

    public function getRescaleSize($sourceFile, $maxwidth, $maxheight, $exifOrientation = null)
    {
        $size =  GetImageSize($sourceFile);

        if ($exifOrientation == 8 ||
           $exifOrientation == 6) {
            $width = $size[1];
            $height = $size[0];
        } else {
            $width = $size[0];
            $height = $size[1];
        }
        $type = $size[2];
        $maxRelation = $maxwidth / $maxheight;
        $relation = $width / $height;
        if ($width <= $maxwidth && $height <= $maxheight) {
            $width2 = $width;
            $height2 = $height;
        } elseif ($width < $maxRelation * $height) { // higher than wider
            $height2 = $maxheight;
            $width2 = (int) ($height2 * $relation);
        } else { // wider than heigher
            $width2 = $maxwidth;
            $height2 = (int) ($width2 / $relation);
        }

        if ($width2 > $maxwidth) {
            \vc\lib\ErrorHandler::error(
                "Calculated width of picture '" . $sourceFile . "' is too big ("
                . $width . "|" . $height . "|" . $width2 . "|" . $maxwidth . ")",
                __FILE__,
                __LINE__
            );
        }
        if ($height2 > $maxheight) {
            \vc\lib\ErrorHandler::error(
                "Calculated height of picture '" . $sourceFile . "' is too big ("
                . $width . "|" . $height . "|" . $height2 . "|" . $maxheight . ")",
                __FILE__,
                __LINE__
            );
        }
        return array($width2, $height2);
    }

    //--------------------------------------------------------------------

    public function rescale($sourceFile, $targetDir, $targetFile, $newWidth, $newHeight, $exifOrientation = 0)
    {
        $size =  GetImageSize($sourceFile);
        if ($exifOrientation == 8 ||
           $exifOrientation == 6) {
            $width = $size[1];
            $height = $size[0];
        } else {
            $width = $size[0];
            $height = $size[1];
        }
        $type = $size[2];

        switch ($size[2]) {
            // GIF
            case IMAGETYPE_GIF:
                $tempImage = @imagecreatefromgif($sourceFile);
                $oldImage = @imagecreatetruecolor($width, $height);
                imagecopy($oldImage, $tempImage, 0, 0, 0, 0, $width, $height);
                imagedestroy($tempImage);
                break;
            // JPG
            case IMAGETYPE_JPEG:
                $oldImage = @imagecreatefromjpeg($sourceFile);

                // Rotating based on exif informations
                if (!empty($oldImage)) {
                    switch ($exifOrientation) {
                        case 8:
                            $oldImage = imagerotate($oldImage, 90, 0);
                            break;
                        case 3:
                            $oldImage = imagerotate($oldImage, 180, 0);
                            break;
                        case 6:
                            $oldImage = imagerotate($oldImage, -90, 0);
                            break;
                    }
                }

                break;
            // PNG
            case IMAGETYPE_PNG:
                $oldImage = @imagecreatefrompng($sourceFile);
                break;
            default:
                \vc\lib\ErrorHandler::error(
                    "Calculated filetype " . $size[2],
                    __FILE__,
                    __LINE__
                );
                return false;
        }

        if (empty($oldImage)) {
            return false;
        }

        $newImage = @imagecreatetruecolor($newWidth, $newHeight);
        @imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        @imagedestroy($oldImage);

        if (!file_exists($targetDir . '/')) {
            mkdir($targetDir . '/');
        }
        imagejpeg($newImage, $targetDir . '/' . $targetFile);
        imagedestroy($newImage);

        return true;
    }

    //--------------------------------------------------------------------

    public function addWatermarkToFile($sourceImage, $targetImage)
    {
        $image = @imagecreatefromjpeg($sourceImage);
        if ($image) {
            $watermarkFile = APP_ROOT . '/pictures/watermark.png';
            $watermarkImage = imagecreatefrompng($watermarkFile);
            $sourceWidth = imagesx($image);
            $sourceHeight = imagesy($image);
            $watermarkWidth = imagesx($watermarkImage);
            $watermarkHeight = imagesy($watermarkImage);

            if ($watermarkWidth > $sourceWidth * self::WATERMARK_RELATION ||
                $watermarkHeight > $sourceHeight * self::WATERMARK_RELATION) {
                $targetWidth = round($sourceWidth * self::WATERMARK_RELATION);
                $targetHeight = $targetWidth * ($watermarkHeight / $watermarkWidth);
            } else {
                $targetWidth = $watermarkWidth;
                $targetHeight = $watermarkHeight;
            }

            imagecopyresampled(
                $image,
                $watermarkImage,
                10,
                $sourceHeight - $targetHeight - 10,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $watermarkWidth,
                $watermarkHeight
            );

            imagejpeg($image, $targetImage);
            return true;
        } else {
            return false;
        }
    }
}
