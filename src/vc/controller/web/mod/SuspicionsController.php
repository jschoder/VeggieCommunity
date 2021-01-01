<?php
namespace vc\controller\web\mod;

class SuspicionsController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Suspicions');

        $suspicionModel = $this->getDbModel('Suspicion');

        $filterType = $request->getInt('type');
        $filterUser = $request->getInt('user');
        $filterIp = $request->getText('ip');
        $filterTimeframe = $request->getText('timeframe');
        $filterLimit = $request->getText('limit');

        $this->getView()->set('filterType', $filterType);
        $this->getView()->set('filterUser', $filterUser);
        $this->getView()->set('filterIp', $filterIp);
        $this->getView()->set('filterTimeframe', $filterTimeframe);
        $this->getView()->set('filterLimit', $filterLimit);

        if (!empty($filterType) || !empty($filterUser) || !empty($filterIp)) {
            if (empty($filterTimeframe) || $filterTimeframe === 'all') {
                $createdSince = null;
            } else {
                $timestamp = new \DateTime();
                if ($filterTimeframe === '24h') {
                    $timestamp->sub(new \DateInterval('P1D'));
                } else if ($filterTimeframe === '7d') {
                    $timestamp->sub(new \DateInterval('P7D'));
                } else if ($filterTimeframe === '1m') {
                    $timestamp->sub(new \DateInterval('P1M'));
                } else if ($filterTimeframe === '3m') {
                    $timestamp->sub(new \DateInterval('P1M'));
                } else {
                    \vc\lib\ErrorHandler::error(
                        'Invalid timeframe: ' . $filterTimeframe,
                        __FILE__,
                        __LINE__
                    );
                }
                $createdSince = $timestamp->getTimestamp();
            }
            $suspicions = $suspicionModel->getSuspicions(
                $filterType,
                $filterUser,
                $filterIp,
                $createdSince,
                $filterLimit
            );
            $this->getView()->set('suspicions', $suspicions);
        } else {
            $hoursSuspicions = $suspicionModel->getAggregatedHoursSuspicions();
            $this->getView()->set('hoursSuspicions', $hoursSuspicions);
            $this->getView()->set('suspicionHoursKeys', $this->getSuspicionKeys($hoursSuspicions));

            $daysSuspicions = $suspicionModel->getAggregatedDaysSuspicions();
            $this->getView()->set('daysSuspicions', $daysSuspicions);
            $this->getView()->set('suspicionDaysKeys', $this->getSuspicionKeys($daysSuspicions));
        }

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/suspicions', true);
    }

    private function getSuspicionKeys($suspicions)
    {
        $suspicionKeys = array();
        foreach ($suspicions as $suspicion) {
            foreach ($suspicion as $suspicionKey => $suspicionValue) {
                if (!in_array($suspicionKey, $suspicionKeys)) {
                    $suspicionKeys[] = $suspicionKey;
                }
            }
        }
        return $suspicionKeys;
    }
}
