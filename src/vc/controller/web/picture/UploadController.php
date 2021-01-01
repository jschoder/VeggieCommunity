<?php
namespace vc\controller\web\picture;

class UploadController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(
                false,
                gettext('upload.noactivesession')
            );
            $this->storePicError(null, 'upload.noactivesession');
            return;
        }

        if (!empty($_FILES['file']['error']))  {
            if ($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE) {
                $status = gettext('upload.files.error.iniSize');
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_FORM_SIZE) {
                $status = gettext('upload.files.error.formSize');
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_PARTIAL) {
                $status = gettext('upload.files.error.partial');
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
                $status = gettext('upload.files.error.noFile');
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_NO_TMP_DIR) {
                $status = gettext('upload.files.error.noTmpDir');
                \vc\lib\ErrorHandler::error(
                    'File couldn\'t be uploaded due to a missing tmp directory.',
                    __FILE__,
                    __LINE__
                );
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_CANT_WRITE) {
                $status = gettext('upload.files.error.cantWrite');
                \vc\lib\ErrorHandler::error(
                    'File can\'t be written to the tmp directory.',
                    __FILE__,
                    __LINE__
                );
            } else if ($_FILES['file']['error'] == UPLOAD_ERR_EXTENSION) {
                $status = gettext('upload.files.error.extension');
                \vc\lib\ErrorHandler::error(
                    'File upload has been stopped by a php extension.',
                    __FILE__,
                    __LINE__
                );
            } else {
                $status = gettext('upload.files.error.unknown') . '(#' . $_FILES['file']['error'] . ')';
                \vc\lib\ErrorHandler::error(
                    'File couldn\'t be uploaded due to an unknown error (#' . $_FILES['file']['error'] . ')',
                    __FILE__,
                    __LINE__
                );
            }
            $this->storePicError($_FILES['file'], $status);
            echo \vc\view\json\View::renderStatus(false, $status);
        } else if (empty($_FILES['file']['tmp_name'])) {
            $this->storePicError($_FILES['file'], 'upload.files.empty');
            echo \vc\view\json\View::renderStatus(
                false,
                gettext('upload.files.empty') .' (#' . $_FILES['file']['error'] . ')'
            );
        } else {
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                if ($_FILES['file']['type'] == 'image/pjpeg' ||
                    $_FILES['file']['type'] == 'image/jpeg' ||
                    $_FILES['file']['type'] == 'image/gif' ||
                    $_FILES['file']['type'] == 'image/png') {
                    $filesize = filesize($_FILES['file']['tmp_name']);
                    if ($filesize > \vc\config\Globals::MAX_UPLOAD_FILE_SIZE) {
                        $this->storePicError($_FILES['file'], 'upload.files.toobig');
                        echo \vc\view\json\View::renderStatus(
                            false,
                            gettext('upload.files.toobig')
                        );
                    } else {
                        $pictureSaveComponent = $this->getComponent('PictureSave');
                        $fileInfo = $pictureSaveComponent->movePic(
                            $_FILES['file']['tmp_name'],
                            TEMP_PIC_DIR . '/full',
                            $this->getSession()->getSetting(\vc\object\Settings::ROTATE_PICS, 1),
                            true,
                            \vc\config\Globals::MAX_PICTURE_WIDTH,
                            \vc\config\Globals::MAX_PICTURE_HEIGHT
                        );
                        if ($fileInfo === false) {
                            // Check for the error and use the correct error message
                            if (GetImageSize($_FILES['file']['tmp_name']) === false) {
                                $this->storePicError($_FILES['file'], 'upload.files.invalidtype #1');
                                echo \vc\view\json\View::renderStatus(
                                    false,
                                    gettext('upload.files.invalidtype')
                                );
                            } else {
                                $this->storePicError($_FILES['file'], 'upload.files.savefailed');
                                echo \vc\view\json\View::renderStatus(
                                    false,
                                    gettext('upload.files.savefailed')
                                );
                            }
                        } else {
                            $status = array(
                                'success' => true,
                                'filename' => $fileInfo['filename'],
                                'sourcename' => prepareHTML($_FILES['file']['name'])
                            );
                            echo \vc\view\json\View::render($status);
                        }
                    }
                } else {
                    $this->storePicError($_FILES['file'], 'upload.files.invalidtype #2');
                    echo \vc\view\json\View::renderStatus(
                        false,
                        gettext('upload.files.invalidtype')
                    );
                }
            } else {
                $this->storePicError($_FILES['file'], 'upload.files.uploadfail');
                echo \vc\view\json\View::renderStatus(
                    false,
                    gettext('upload.files.uploadfail')
                );
            }
        }
    }

    private function storePicError($file, $message)
    {
        $_SESSION['pic_errors'] = array();
        if (!array_key_exists('pic_errors', $_SESSION)) {
            $_SESSION['pic_errors'] = array();
        }

        $_SESSION['pic_errors'][] =
            'Controller: Upload' . "\n" .
            'Message: ' . $message . "\n" .
            'File: ' . var_export($file, true);
    }
}
