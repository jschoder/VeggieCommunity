<?php
namespace vc\controller\web\account;

class StatusController extends \vc\controller\web\AbstractWebController
{
    protected function logPageView()
    {
        return false;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $return = array();

        if ($this->getSession()->hasActiveSession()) {
//            $pmThreadModel = $this->getDbModel('PmThread');
//            $return['messages'] = $pmThreadModel->getUnreadThreads($this->getSession()->getUserId());

// :TODO: JOE - use or kill
            $pmModel = $this->getDbModel('Pm');
            $return['messages'] = $pmModel->getCount(
                array(
                    'recipientid' => $this->getSession()->getUserId(),
                    'recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_NEW
                )
            );

            $return['friends'] = count($this->getOwnFriendsToConfirm());

            $return['events'] = 0;

            $groupNotificationModel = $this->getDbModel('GroupNotification');
            $return['groups'] = $groupNotificationModel->getNotificationsCount($this->getSession()->getUserId());

            if ($this->design === 'lemongras') {
                $return['tickets'] = 0;
            } else {
                $helpNotificationModel = $this->getDbModel('HelpNotification');
                $return['tickets'] = $helpNotificationModel->getCount(
                    array(
                        'profile_id' => $this->getSession()->getUserId()
                    )
                );
            }

            if ($this->getSession()->isAdmin()) {
                $return['mod'] = array();

                $ticketModel = $this->getDbModel('Ticket');
                $return['mod']['tickets'] = $ticketModel->getCount(
                    array('status' => \vc\object\Ticket::STATUS_OPEN)
                );

                $pmModel = $this->getDbModel('Pm');
                $return['mod']['pm'] = $pmModel->getCount(
                    array('recipientstatus' => \vc\object\Mail::RECIPIENT_STATUS_UNSENT)
                );

                $return['mod']['spam'] = $pmModel->getSpamCount();

                $flagModel = $this->getDbModel('Flag');
                $return['mod']['flag'] = $flagModel->getCount(
                    array(
                        'aggregate_type !=' => \vc\config\EntityTypes::GROUP_FORUM,
                        'processed_at IS NULL'
                    )
                );

                $realCheckModel = $this->getDbModel('RealCheck');
                $return['mod']['real'] = $realCheckModel->getCount(
                    array(
                        'status' => array(
                            \vc\object\RealCheck::STATUS_SUBMITTED,
                            \vc\object\RealCheck::STATUS_REOPENED
                        )
                    )
                );

                $groupModel = $this->getDbModel('Group');
                $return['mod']['groups'] = $groupModel->getCount(
                    array(
                        'confirmed_at IS NULL',
                        'deleted_at IS NULL'
                    )
                );

                $pictureChecklistModel = $this->getDbModel('PictureChecklist');
                $return['mod']['picsUnchecked'] = $pictureChecklistModel->getCount(array());

                $pictureWarningModel = $this->getDbModel('PictureWarning');
                $return['mod']['picsPrewarned'] = $pictureWarningModel->getCount(
                    array(
                        'own_pic_confirmed_at IS NULL',
                        'deleted_at IS NULL'
                    ),
                    array(
                        'INNER JOIN vc_picture ON vc_picture.id = vc_picture_warning.picture_id',
                        'INNER JOIN vc_profile ON vc_profile.id = vc_picture_warning.profile_id
                                    AND vc_profile.active > 0'
                    )
                );

                $toldafriendModel = $this->getDbModel('Toldafriend');
                $return['mod']['toldafriend'] = $toldafriendModel->getCount(array('is_sent' => 0));
            }
        } else {
            // Might be logged out on different tab. So this is a totally legit request
            $return['messages'] = 0;
            $return['friends'] = 0;
            $return['events'] = 0;
            $return['groups'] = 0;
            $return['tickets'] = 0;
        }

        echo \vc\view\json\View::render($return);
    }
}
