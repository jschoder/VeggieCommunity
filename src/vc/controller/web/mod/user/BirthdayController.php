<?php
namespace vc\controller\web\mod\user;

class BirthdayController extends \vc\controller\web\AbstractWebController
{
    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        if (empty($_POST['birthday']) || empty($_POST['profileid'])) {
            throw new \vc\exception\NotFoundException();
        }

        $profileId = intval($_POST['profileid']);
        $dateTime = \DateTime::createFromFormat(
            gettext('javascript.ui.datepicker.php'),
            $_POST['birthday']
        );

        if ($dateTime === false) {
            $notification = $this->setNotification(
                self::NOTIFICATION_ERROR,
                'Invalid date'
            );
            throw new \vc\exception\RedirectException(
                $this->path . 'user/view/' . $profileId . '/mod/?notification=' . $notification
            );
        }

        $age = $dateTime->diff(new \DateTime('now'))->y;

        $profileModel = $this->getDbModel('Profile');
        $profileModel->update(
            array(
                'id' => $profileId
            ),
            array(
                'birth' => date('Y-m-d', $dateTime->getTimestamp()),
                'age' => $age
            )
        );

        $matchingModel = $this->getDbModel('Matching');
        $matchingModel->recalculate($profileId);

        $notification = $this->setNotification(
            self::NOTIFICATION_SUCCESS,
            'Birthday and age have been updated'
        );
        throw new \vc\exception\RedirectException(
            $this->path . 'user/view/' . $profileId . '/mod/?notification=' . $notification
        );
    }
}
