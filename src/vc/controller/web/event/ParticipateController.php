<?php
namespace vc\controller\web\event;

class ParticipateController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            echo \vc\view\json\View::renderStatus(false, gettext('event.participate.noactivesession'));
            return;
        }

        if ($this->isSuspicionBlocked()) {
            echo \vc\view\json\View::renderStatus(false, gettext('suspicion.blocked'));
            return;
        }

        $formValues = array_merge($_POST);
        if (empty($formValues['id']) ||
            empty($formValues['degree'])) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_POST_REQUEST,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $eventModel = $this->getDbModel('Event');
        $eventObject = $eventModel->loadObject(array('hash_id' => $formValues['id']));
        if (empty($eventObject)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_INVALID_EVENT,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        if (!$eventModel->canSeeEvent($this->getSession()->getUserId(), $eventObject)) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_INVISIBLE_EVENT,
                array(
                    'formValues' => $formValues
                )
            );
            echo \vc\view\json\View::renderStatus(false);
            return;
        }

        $eventParticipantModel = $this->getDbModel('EventParticipant');
        $updated = $eventParticipantModel->setParticipation(
            $eventObject->id,
            $this->getSession()->getUserId(),
            intval($formValues['degree'])
        );

        $cacheModel = $this->getModel('Cache');
        $cacheModel->resetProfileRelations($this->getSession()->getUserId());

        if ($updated) {
            $subscriptionModel = $this->getDbModel('Subscription');
            $subscriptionModel->add(
                $this->getSession()->getUserId(),
                \vc\config\EntityTypes::EVENT,
                $eventObject->id
            );
            echo \vc\view\json\View::renderStatus(true);
        } else {
            echo \vc\view\json\View::renderStatus(false);
        }
    }
}
