<?php
namespace vc\controller\web\mod;

class ErrorController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Errors');
        
        
        if (count($this->siteParams) > 0 && 
            preg_match('~^[0-9]{4}-[0-9]{2}-[0-9]{2}(.zip)?$~', $this->siteParams[0])) {
            
            if (count($this->siteParams) > 1) {
                $this->getView()->set(
                    'errorDetails', 
                    $this->getErrorDetails($this->siteParams[0], $this->siteParams[1])
                );
            } else {
                $this->getView()->set('errorFile', $this->siteParams[0]);
                $this->getView()->set('errors', $this->getErrors($this->siteParams[0]));
            }
        } else {
            $this->getView()->set('errorFiles', $this->getErrorFiles());
        }

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/errors', true);
    }
    
    private function getErrorFiles()
    {
        $errorFiles = array();
        foreach (scandir(APP_REPORTS) as $file) {
            $path = APP_REPORTS . '/' . $file;
            if ($file !== '.' && $file !== '..' && is_dir($path)) {
                $errorFiles[$file] = count(scandir($path)) - 2;
            } else if(substr($file, -4) === '.zip') {
                $archive = new \ZipArchive();
                $archive->open($path);
                $errorFiles[$file] = $archive->numFiles;
                $archive->close();
            }
        }
        return array_reverse($errorFiles);
    }
    
    private function getErrors($source) {
        $errorSources = $this->getErrorSources($source);
        $errors = array();
        foreach ($errorSources as $errorSource) {
            $error = explode("\n", $errorSource);
            
            if (count($error) > 5 &&
                strpos($error[5], 'str: ') === 0 &&
                strpos($error[6], 'file: ') === 0) {
                
                $key = sha1($error[5] . ' ### ' . $error[6]);
            
                if (empty($errors[$key])) {
                    $errors[$key] = array(
                        'str' => substr($error[5], 4),
                        'file' => substr($error[6], 5),
                        'count' => 1
                    );
                } else {
                    $errors[$key]['count'] = $errors[$key]['count'] + 1;
                }
            }
        }
        return $errors;
    }
    
    private function getErrorDetails($source, $hash) {
        $errorSources = $this->getErrorSources($source);
        $errorDetails = array();
        foreach ($errorSources as $file => $errorSource) {
            $error = explode("\n", $errorSource);
            
            if (count($error) > 5 &&
                strpos($error[5], 'str: ') === 0 &&
                strpos($error[6], 'file: ') === 0) {
                
                $key = sha1($error[5] . ' ### ' . $error[6]);
                if ($key === $hash) {
                    $errorDetails[$file] = $errorSource;
                }
            }
        }
        return $errorDetails;
    }
    
    private function getErrorSources($source) {
        $errorSources = array();
        if (substr($source, -4) === '.zip') {
            $archive = new \ZipArchive();
            $archive->open(APP_REPORTS . '/' . $source);
            for ($i = 0; $i < $archive->numFiles; $i++) {
                $file = array_pop(explode('/', $archive->getNameIndex($i)));
                $errorSources[$file] = $archive->getFromIndex($i);
            }
            $archive->close();
        } else {
            foreach (scandir(APP_REPORTS . '/' . $source) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $errorSources[$file] = file_get_contents(APP_REPORTS . '/' . $source . '/' . $file);
                }
            }
        }
        return $errorSources;
    }
}
