<?php
namespace vc\controller\web\pm;

class ThreadController extends \vc\controller\web\AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('mailbox.noactivesession'));
            return;
        }
        $before = $request->getInt('before', null);
        $after = $request->getInt('after', null);
        $filter = $request->getTextArray('f');

        $pmThreadModel = $this->getDbModel('PmThread');
        $threads = $pmThreadModel->getThreads(
            $this->getSession()->getUserId(),
            !empty($before) && empty($after) ? \vc\config\Globals::THREAD_COUNT_LOAD: null,
            $before,
            $after,
            (empty($filter['name']) ? null : $filter['name']),
            (empty($filter['unread']) ? false : boolval($filter['unread']))
        );

        foreach ($threads as $i => $thread) {
            $threads[$i]['lastMessage'] = prepareHTML($thread['lastMessage']);
            switch ($thread['contact']['gender']) {
                case 2:
                    $threads[$i]['contact']['gender'] = 'm';
                    break;
                case 4:
                    $threads[$i]['contact']['gender'] = 'f';
                    break;
                case 6:
                    $threads[$i]['contact']['gender'] = 'o';
                    break;
                default:
                    $threads[$i]['contact']['gender'] = 'a';
            }
        }
        $return = array(
            'threads' => $threads,
        );
        if ($before !== null && $after === null) {
            $return['isLast'] = (count($threads) < \vc\config\Globals::THREAD_COUNT_LOAD);
        }
        echo \vc\view\json\View::render($return);
    }
}
