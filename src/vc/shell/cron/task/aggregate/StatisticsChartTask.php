<?php
namespace vc\shell\cron\task\aggregate;

class StatisticsChartTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        if (!$this->isTestMode()) {
            $this->updateChart('de');
            $this->updateChart('en');
        }
    }

    private function updateChart($locale)
    {
        $filename = APP_ROOT . '/web/img/chart/statistics-' . $locale . '.png';
        $chartHelper = new \vc\helper\StartChartHelper();
        $chartHelper->updateChart(
            $this->getDbModel('Profile'),
            $this->getComponent('I14n'),
            $this->getModel('Cache'),
            $locale,
            $filename
        );
        exec('optipng -o6 -quiet -preserve "' . $filename . '"');
    }
}
