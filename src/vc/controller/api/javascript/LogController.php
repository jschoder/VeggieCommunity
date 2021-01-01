<?php
namespace vc\controller\api\javascript;

class LogController extends \vc\controller\api\AbstractApiController
{
    protected function logPageView()
    {
        return false;
    }

    public function handlePost(\vc\controller\Request $request)
    {
        $javascriptLogModel = $this->getDbModel('JavascriptLog');
        $url = $request->getText('url', '');
        // Cut off domain if expected root
        if (strpos($url, 'https://www.veggiecommunity.dev/') === 0 ||
            strpos($url, 'https://www.veggiecommunity.org/') === 0) {
            $url = substr($url, 32);
        }

        $javascriptLogModel->insert(
            $this->getSession()->getUserId(),
            empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
            $url,
            $request->getText('line', ''),
            $request->getText('msg', '')
        );
    }
}
