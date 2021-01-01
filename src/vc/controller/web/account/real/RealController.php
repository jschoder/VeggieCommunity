<?php
namespace vc\controller\web\account\real;

class RealController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\LoginRequiredException();
        }

        $this->setTitle(gettext('real.title'));

        $realCheckModel = $this->getDbModel('RealCheck');
        $openRealCheckObject = $realCheckModel->loadObject(array(
            'profile_id' => $this->getSession()->getUserId(),
            'created_at >' => date('Y-m-d H:i:s', time() - 86400),
            'status' => \vc\object\RealCheck::STATUS_OPEN
        ));
        $submittedRealCheckObject = $realCheckModel->loadObject(array(
            'profile_id' => $this->getSession()->getUserId(),
            'status' => \vc\object\RealCheck::STATUS_SUBMITTED
        ));

        if ($openRealCheckObject === null) {
            $confirmationForm = null;
        } else {
            $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
            $confirmationForm = new \vc\form\Form(
                $formId,
                'Confirmation',
                $this->path,
            $this->locale,
                'account/real/confirm/'
            );
            $confirmationForm->add(new \vc\form\InfoText(
                'notifyInfo',
                '<p>' . gettext('real.infotext') . '</p>' .
                '<ul class="list">' .
                    '<li>' . gettext('real.infotext.step.profilepics') . '</li>' .
                    '<li>' . gettext('real.infotext.step.code') . '</li>' .
                    '<li><strong>' .
                        gettext('real.step2.yourCode') . ': ' .
                        $openRealCheckObject->code  . '<br />' .
                        gettext('real.step2.validtill') . ': ' .
                        $this->getView()->prepareDate(
                            gettext('real.step2.validtill.dateformat'),
                            strtotime($openRealCheckObject->createdAt) + 86400
                        ) .
                    '</strong></li>' .
                    '<li><strong>' . gettext('real.infotext.step.code.paper') . '</strong></li>' .
                    '<li><strong>' . gettext('real.infotext.step.code.picture') . '</strong></li>' .
                    '<li><strong>' . gettext('real.infotext.step.code.upload') . '</strong></li>' .
                    '<li>' . gettext('real.infotext.step.confirm') . '</li>' .
                    '<li>' . gettext('real.infotext.step.time') . '</li>' .
                '</ul>'
            ));
            $confirmationForm->add(new \vc\form\Image(
                'picture',
                'picture',
                gettext('real.step2.image'),
                gettext('real.step2.image.help')
            ))->setMandatory(true);
            $confirmationForm->add(new \vc\form\Submit(
                gettext('real.step2.confirm')
            ));
            $this->setForm($confirmationForm);
        }

        $this->getView()->set('openRealCheck', $openRealCheckObject);
        $this->getView()->set('confirmationForm', $confirmationForm);
        $this->getView()->set('submittedRealCheckObject', $submittedRealCheckObject);
        echo $this->getView()->render('account/real', true);
    }
}
