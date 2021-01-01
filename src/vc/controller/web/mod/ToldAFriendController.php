<?php
namespace vc\controller\web\mod;

class ToldAFriendController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $toldafriendModel = $this->getDbModel('Toldafriend');
        $toldafriendObjects = $toldafriendModel->loadObjects(array('is_sent' => 0));

//        $fieldMap = $toldafriendModel->getFieldMap(
//            'id',
//            'body',
//            array('is_sent' => 0),
//            array(),
//            'id ASC'
//        );

        $this->setTitle('Told a Friend');

        $this->getView()->set('toldafriendObjects', $toldafriendObjects);
        echo $this->getView()->render('mod/toldafriend', true);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $ids = $request->getIntArray('id');

        $sendIds = array();
        $deniedIds = array();
        foreach ($ids as $id => $status) {
            if ($status === \vc\object\ToldAFriend::STATUS_SENT) {
                $sendIds[] = intval($id);
            } elseif ($status === \vc\object\ToldAFriend::STATUS_DENIED) {
                $deniedIds[] = intval($id);
            }
        }


        $toldafriendModel = $this->getDbModel('Toldafriend');
        if (!empty($sendIds)) {
            $toldafriendObjects = $toldafriendModel->loadObjects(array('id' => $sendIds));
            foreach ($toldafriendObjects as $toldafriendObject) {
                $sent = true;
                for ($i = 1; $i <= 6; $i++) {
                    $recieverName = 'reciever' . $i;
                    if (!empty($toldafriendObject->$recieverName)) {
                        $subject = $toldafriendObject->subject;
                        if (empty($subject)) {
                            $subject = "I've found a great page for vegetarians and vegans!";
                        }

                        $systemMessageModel = $this->getDbModel('SystemMessage');
                        $success = $systemMessageModel->add(
                            $toldafriendObject->$recieverName,
                            $subject,
                            $toldafriendObject->body
                        );
                        if ($success === false) {
                            $sent = false;
                        }
                    }
                }

                if ($sent) {
                    $toldafriendModel->update(
                        array(
                            'id' => $toldafriendObject->id
                        ),
                        array(
                            'is_sent' => \vc\object\ToldAFriend::STATUS_SENT
                        )
                    );
                }
            }
        }
        if (!empty($deniedIds)) {
            $toldafriendModel->update(
                array(
                    'id' => $deniedIds
                ),
                array(
                    'is_sent' => \vc\object\ToldAFriend::STATUS_DENIED
                ),
                false
            );
        }

        throw new \vc\exception\RedirectException($this->path . 'mod/toldafriend/');
    }
}
