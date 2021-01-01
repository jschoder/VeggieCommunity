<?php
namespace vc\controller\web\poll;

class ViewController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setFullPage(false);

        if ($this->getSession()->hasActiveSession() &&
            count($this->siteParams) > 0) {
            if (count($this->siteParams) > 1 &&
               $this->siteParams[1] == 'form') {
                $this->getView()->set('type', 'form');
            } else {
                $this->getView()->set('type', 'result');
            }

            $pollModel = $this->getDbModel('Poll');
            $poll = $pollModel->loadPoll(
                $this->locale,
                $this->getSession()->getProfile(),
                $this->siteParams[0]
            );
            if ($poll !== null) {
                $this->getView()->set('poll', $poll);
                echo $this->getView()->render('poll/view', false);
            }
        }
    }
}
