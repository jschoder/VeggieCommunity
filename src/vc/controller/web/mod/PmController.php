<?php
namespace vc\controller\web\mod;

class PmController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $pmModel = $this->getDbModel('Pm');
        if (count($this->siteParams) == 1) {
            $messages = $pmModel->getAllPms(
                $this->siteParams[0]
            );
        } elseif (count($this->siteParams) == 2) {
            $messages = $pmModel->getAllPms(
                $this->siteParams[0],
                $this->siteParams[1]
            );
        } elseif (count($this->siteParams) == 3) {
            $messages = $pmModel->getAllPms(
                $this->siteParams[0],
                $this->siteParams[1],
                $this->siteParams[2]
            );
        } else {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Messages #' . intval($this->siteParams[0]));

        $this->getView()->set('senderId', $this->siteParams[0]);
        $this->getView()->set('messages', $messages);
        echo $this->getView()->render('mod/pm', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin() ||
            count($this->siteParams) < 2) {
            throw new \vc\exception\NotFoundException();
        }

        $formValues = $_POST;
        $pmModel = $this->getDbModel('Pm');
        $pmModel->update(
            array(
                'id' => intval($formValues['id']),
            ),
            array(
                'recipientstatus' => intval($formValues['status'])
            )
        );
        throw new \vc\exception\RedirectException(
            $this->path . 'mod/pm/' . $this->siteParams[0] . '/' . $this->siteParams[1] . '/500/'
        );
    }
}
