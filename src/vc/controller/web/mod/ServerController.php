<?php
namespace vc\controller\web\mod;

class ServerController extends \vc\controller\web\AbstractWebController
{
    public function handleGet(\vc\controller\Request $request)
    {
        if (!$this->getSession()->isAdmin()) {
            throw new \vc\exception\NotFoundException();
        }

        $this->setTitle('Serverstatus');

        try {
            $options = array(
                'profile' => '2.4',
                'prefix'  => 'vc:' . \vc\config\Globals::VERSION . ':',
            );
            $redis = new \Predis\Client('tcp://127.0.0.1', $options);

            $redisInfo = $redis->info();
            $this->getView()->set('redisInfo', $redisInfo);

        } catch (\Exception $ex) {
            $this->getView()->set('redisInactive', true);
        }

        $this->getView()->set('sysloadavg', sys_getloadavg());

        $this->readProc('procMeminfo', '/proc/meminfo', ':');
        $this->readProc('procStat', '/proc/stat', ' ');
        $this->readNetwork();
        $this->readFilesystem();

        $this->getView()->set('wideContent', true);
        echo $this->getView()->render('mod/server', true);
    }

    private function readProc($viewVariable, $procPath, $splitChar)
    {
        $content = (file_get_contents($procPath));
        $values = array();
        foreach (explode("\n", $content) as $line) {
            $lineValues = explode($splitChar, $line);
            $variableName = trim(array_shift($lineValues));
            $variableValue = trim(implode(':', $lineValues));
            if (substr($variableValue, -3) == ' kB') {
                $variableValue = intval(substr($variableValue, 0, strlen($variableValue) - 3)) * 1024;
            }

            $values[$variableName] = $variableValue;
        }
        $this->getView()->set($viewVariable, $values);
        return $values;
    }

    private function readNetwork()
    {
        $networkDevices = $this->readProc('procNet', '/proc/net/dev', ':');
        $networkBytes = array();
        foreach (array_keys($networkDevices) as $networkDevice) {
            if (!empty($networkDevice) && strpos($networkDevice, ' ') === FALSE) {
                $networkBytes[$networkDevice] = array(
                    'rx' => intval(file_get_contents('/sys/class/net/' . $networkDevice . '/statistics/rx_bytes')),
                    'tx' => intval(file_get_contents('/sys/class/net/' . $networkDevice . '/statistics/tx_bytes'))
                );
            }
        }
        $this->getView()->set('networkBytes', $networkBytes);
    }

    private function readFilesystem()
    {
        $cacheModel = $this->getModel('Cache');
        $cachedMounted = json_decode($cacheModel->get('filesystem'), true);
        $this->getView()->set('filesystem', $cachedMounted);
    }
}
