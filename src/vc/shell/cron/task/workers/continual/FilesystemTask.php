<?php
namespace vc\shell\cron\task\workers\continual;

class FilesystemTask extends \vc\shell\cron\task\AbstractCronTask
{
    public function execute()
    {
        $filesystem = explode("\n", shell_exec('df -P'));
        array_shift($filesystem);
        $mounted = array();
        foreach ($filesystem as $line) {
            $values = array_values(array_filter(explode(' ', $line), function($value) { return $value !== ''; }));
            if (count($values) === 6) {
                $mounted[$values[5]] = array(
                    'used' => $values[2],
                    'available' => $values[3],
                    'capacity' => $values[4]
                );
            }
        }
        ksort($mounted);

        $cacheModel = $this->getModel('Cache');
        $cacheModel->set('filesystem', json_encode($mounted));
    }
}
