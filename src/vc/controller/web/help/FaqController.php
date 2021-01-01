<?php
namespace vc\controller\web\help;

class FaqController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('menu.help'));
        $this->getView()->set('activeMenuitem', 'help');
        $this->getView()->setHeader('robots', 'noindex, follow');

        $faqModel = $this->getDbModel('Faq');
        $faqs = $faqModel->loadObjects(
            array(
                'locale' => $this->locale
            )
        );
        $this->getView()->set('faqs', $faqs);
        echo $this->getView()->render('help/faq', true);
    }
}
