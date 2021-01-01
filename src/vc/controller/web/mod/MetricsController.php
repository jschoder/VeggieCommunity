<?php
namespace vc\controller\web\mod;

class MetricsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $metricModel = $this->getDbModel('Metric');
        $metricData = $metricModel->getMetricData();

        $this->setTitle('Metrics');

        $this->getView()->set('metricData', $metricData);

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/metrics', true);
    }
}
