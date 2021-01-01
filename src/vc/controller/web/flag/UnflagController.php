<?php
namespace vc\controller\web\flag;

class UnflagController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('unflag.noactivesession'));
            return;
        }

        $formValues = $_POST;

        if (empty($formValues['id'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false, gettext('unflag.failed'));
            return;
        }

        $flagModel = $this->getDbModel('Flag');
        $affectedRows = $flagModel->update(
            array(
                'hash_id' => $formValues['id'],
                'processed_at IS NULL'
            ),
            array(
                'processed_by' => intval($this->getSession()->getUserId()),
                'processed_at' => date('Y-m-d H:i:s')
            )
        );

        if ($affectedRows == 1) {
            echo \vc\view\json\View::renderStatus(true);
        } else {
            // Check if somebody might have already unflagged this
            $flagObject = $flagModel->loadObject(array('hash_id' => $formValues['id']));
            if (empty($flagObject)) {
                // The flag doesn't even exist. Somebody is trying to hack the system
                $this->addSuspicion(
                    \vc\model\db\SuspicionDbModel::TYPE_INVALID_FLAG,
                    array(
                        'flagHashId' => $formValues['id']
                    )
                );
                echo \vc\view\json\View::renderStatus(false, gettext('unflag.failed'));
            } else {
                echo \vc\view\json\View::renderStatus(true);
            }
        }
    }
}
