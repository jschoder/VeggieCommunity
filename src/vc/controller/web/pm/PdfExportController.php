<?php
namespace vc\controller\web\pm;

class PdfExportController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (empty($this->siteParams) || !$this->getSession()->hasActiveSession()) {
            throw new \vc\exception\NotFoundException();
        }

        if ($this->getSession()->getPlusLevel() === null ||
            $this->getSession()->getPlusLevel() < \vc\object\Plus::PLUS_TYPE_STANDARD) {
            $this->addSuspicion(
                \vc\model\db\SuspicionDbModel::TYPE_ACCESS_PLUS_ONLY_FEATURES,
                array(
                    'siteParams' => $this->siteParams
                )
            );
            throw new \vc\exception\NotFoundException();
        }

        set_time_limit(0);

        $pdfComponent = $this->getComponent('Pdf');
        $pdfComponent->export(
            $this->locale,
            $this->getSession()->getUserId(),
            intval($this->siteParams[0])
        );
    }
}
