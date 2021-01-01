<?php
namespace vc\controller\web;

class JsController extends AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if ($this->getServer() == 'local') {
            $contents = $this->getContents();
        } else {
            $cacheModel = $this->getModel('Cache');
            $contents = $cacheModel->getJsCssCache(
                'JS',
                $this->locale,
                $this->design
            );

            if ($contents === null) {
                $contents = $this->getContents();
                $cacheModel->setJsCssCache(
                    'JS',
                    $this->locale,
                    $this->design,
                    $contents
                );
            }
        }

        header('Content-Type: text/javascript', true);
        header('Expires: Wed, 01 Jan 2025 01:00:00 GMT', true);
        header('Last-Modified: Wed, 01 Jan 2015 01:00:00 GMT', true);
        header('Cache-Control:max-age=31536000', true);
        header_remove('Pragma');
        echo $contents;
    }

    private function getContents()
    {
        $contents = $this->getPrecompressedContents() . $this->getCompressedContents();
        while (strpos($contents, '/*') !== false) {
            $startIndex = strpos($contents, '/*');
            $endIndex = strpos($contents, '*/', $startIndex + 1);
            $contents = substr($contents, 0, $startIndex) .
                        substr($contents, $endIndex + 2);
        }
        return $contents;
    }

    private function getPrecompressedContents()
    {
        $contents = '';
        foreach (\vc\config\Globals::$jsFiles['precompressed'] as $file) {
            $contents .= file_get_contents(APP_ROOT . '/js/' . $file);
        }
        return $contents;
    }

    private function getCompressedContents()
    {
        if (file_exists(APP_ROOT . '/js/vc.min.js')) {
            $contents = file_get_contents(APP_ROOT . '/js/vc.min.js');
        } else {
            $contents = '';
            foreach (\vc\config\Globals::$jsFiles['uncompressed'] as $file) {
                $contents .= file_get_contents(APP_ROOT . '/js/' . $file);
            }
        }

        // Replacing locales
        $contents = preg_replace_callback('~%GETTEXT\(\'([^\']*)\'\)%~', array($this, 'gettext'), $contents);

        // Replace standard variables
        $replacements = array();
        $replacements['%PATH%'] = $this->path;

        $replacements['%ENTITY_TYPE_PROFILE%'] = \vc\config\EntityTypes::PROFILE;
        $replacements['%ENTITY_TYPE_PROFILE_PICTURE%'] = \vc\config\EntityTypes::PROFILE_PICTURE;
        $replacements['%ENTITY_TYPE_GROUP%'] = \vc\config\EntityTypes::GROUP;
        $replacements['%ENTITY_TYPE_GROUP_MEMBER_REQUEST%'] = \vc\config\EntityTypes::GROUP_MEMBER_REQUEST;
        $replacements['%ENTITY_TYPE_GROUP_INVITATION%'] = \vc\config\EntityTypes::GROUP_INVITATION;
        $replacements['%ENTITY_TYPE_GROUP_FORUM%'] = \vc\config\EntityTypes::GROUP_FORUM;
        $replacements['%ENTITY_TYPE_FORUM_THREAD%'] = \vc\config\EntityTypes::FORUM_THREAD;
        $replacements['%ENTITY_TYPE_FORUM_COMMENT%'] = \vc\config\EntityTypes::FORUM_COMMENT;
        $replacements['%ENTITY_TYPE_EVENT%'] = \vc\config\EntityTypes::EVENT;
        $replacements['%ENTITY_TYPE_PM%'] = \vc\config\EntityTypes::PM;
        $replacements['%ENTITY_TYPE_STATUS%'] = \vc\config\EntityTypes::STATUS;
        $replacements['%ENTITY_TYPE_CHAT%'] = \vc\config\EntityTypes::CHAT;

        $replacements['%LAST_VISITOR_XS%'] = \vc\config\Globals::LAST_VISITORS_XS;
        $replacements['%LAST_VISITOR_XL%'] = \vc\config\Globals::LAST_VISITORS_XL;

        $replacements['%WEBSOCKET_SERVER%'] = \vc\config\Globals::$websocket[$this->getServer()]['host'];
        $replacements['%WEBSOCKET_PORT%'] = \vc\config\Globals::$websocket[$this->getServer()]['port'];
        if (\vc\config\Globals::$websocket[$this->getServer()]['active']) {
            $replacements['%WEBSOCKET_ACTIVE%'] = 'true';
        } else {
            $replacements['%WEBSOCKET_ACTIVE%'] = 'false';
        }

        $replacements['%FACEBOOK_APP%'] = \vc\config\Globals::$apps[$this->getServer()]['facebook']['appid'];

        $replacements['%FILTER_KEYWORDS%'] = base64_encode(json_encode(\vc\config\Globals::$filterKeywords));

        $contents = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        );

        return $contents;
    }

    public function gettext($match)
    {
        return gettext($match[1]);
    }
}
