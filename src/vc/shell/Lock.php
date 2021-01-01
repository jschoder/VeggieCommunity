<?php
namespace vc\shell;

class Lock
{
    private $lockFile;

    private $fp;

    public function __construct($lockFile)
    {
        $this->lockFile = $lockFile;
    }

    public function create()
    {
        if (!file_exists($this->lockFile)) {
            file_put_contents($this->lockFile, '.');
        }

        $this->fp = fopen($this->lockFile, 'r');
        return flock($this->fp, LOCK_EX | LOCK_NB);
    }

    public function destruct()
    {
        flock($this->fp, LOCK_UN);
        fclose($this->fp);
    }
}
