<?php
namespace vc\controller\web\mod;

class MessengerController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $form = $this->createForm();
        $this->view($form);
    }

    public function handlePost(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $systemMessageModel = $this->getDbModel('SystemMessage');
        $query = 'SELECT vc_profile.id, vc_profile.nickname, vc_profile.email
	              FROM vc_profile
                  INNER JOIN vc_setting lang ON lang.profileid = vc_profile.id AND lang.field = 31
                  ' . $request->getText('join') . '
                  WHERE ' . $request->getText('filter') . ' AND vc_profile.active > 0 ORDER BY vc_profile.id ASC';
        $result = $this->getDb()->select($query);
        $links = '<ul class="list">';
        while ($row = $result->fetch_row()) {
            $links .= '<li><a href="' . intval($row[0]) . '">' . prepareHTML($row[1]) . '</a></li>';

            if ($request->getBoolean('send')) {
                $systemMessageModel->add(
                    $row[2],
                    $request->getText('subject'),
                    str_replace('%NICKNAME%', $row[1], $request->getText('message'))
                );
            }
        }
        $links .= '</ul>';
        $result->free();
        $this->getView()->set('links', $links);

        $form = $this->createForm();

        $form->setDefaultValues(array(
            'filter' => $request->getText('filter'),
            'join' => $request->getText('join'),
            'subject' => $request->getText('subject'),
            'message' => $request->getText('message'),
            'send' => 1
        ));
        $this->view($form);
    }

    public function createForm()
    {
        $formId = sha1($this->getSession()->getUserId() . time() . rand(0, 999999));
        $form = new \vc\form\Form(
            $formId,
            'Messenger',
            $this->path,
            $this->locale,
            'mod/messenger/',
            1.0
        );

        $form->add(new \vc\form\Text(
            null,
            'filter',
            'Filter',
            10000,
            null,
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            null,
            'join',
            'Join',
            10000,
            null,
            \vc\form\Text::TEXTAREA
        ));
        $form->add(new \vc\form\Text(
            null,
            'subject',
            'Subject',
            255
        ))->setMandatory(true);
        $form->add(new \vc\form\Text(
            null,
            'message',
            'Message',
            10000,
            null,
            \vc\form\Text::TEXTAREA
        ))->setMandatory(true);
        $form->add(new \vc\form\Checkbox(
            null,
            'send',
            'Send',
            false
        ));

        $form->add(new \vc\form\Submit('Submit'));

        return $form;
    }

    private function view($form)
    {
        $this->setTitle('Admin Messenger');
        $this->setForm($form);
        $this->getView()->set('form', $form);
        echo $this->getView()->render('mod/messenger', true);
    }
}
