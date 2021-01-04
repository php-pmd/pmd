<?php

namespace PhpPmd\Pmd\Log;

class FileLogger implements LoggerInterface
{
    protected $file;

    public function __construct($name)
    {
        $this->file = $name;
        $dir = PMD_HOME . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            $res = mkdir($dir, 0777, true);
            if (!$res) {
                throw new \Exception('Create ' . $dir . ' fail.');
            }
        }
    }

    protected function getFile()
    {
        $file = PMD_HOME . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . "{$this->file}.log";
        if (!file_exists($file)) \touch($file);
        return $file;
    }

    public function write($msg)
    {
        @file_put_contents($this->getFile(), $msg, FILE_APPEND);
    }

    public function info($msg)
    {
        $time = date('Y-m-d H:i:s');
        $this->write("[INFO] - [{$time}] \n{$msg}");
    }

    public function error($msg)
    {
        $time = date('Y-m-d H:i:s');
        $this->write("[ERROR] - [{$time}] \n{$msg}");
    }

    public function debug($msg)
    {
        $time = date('Y-m-d H:i:s');
        $this->write("[DEBUG] - [{$time}] \n{$msg}");
    }

    public function trace($msg)
    {
        $time = date('Y-m-d H:i:s');
        $this->write("[TRACE] - [{$time}] \n{$msg}");
    }

    public function warning($msg)
    {
        $time = date('Y-m-d H:i:s');
        $this->write("[WARNING] - [{$time}] \n{$msg}");
    }

    public function writeln($msg)
    {
    }

    public function close()
    {
    }
}