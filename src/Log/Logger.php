<?php

namespace PhpPmd\Pmd\Log;

class Logger implements LoggerInterface
{
    protected $file;

    protected $_outputStream;

    protected $_outputDecorated;

    public function __construct($logFile)
    {
        $this->file = $logFile;
        if (!file_exists($this->file)) \touch($this->file);
    }

    public function logDump()
    {
        global $STDOUT, $STDERR;
        \set_exception_handler(function () {
        });
        \fclose(\STDOUT);
        \fclose(\STDERR);
        $STDOUT = \fopen($this->file, "a+");
        $STDERR = \fopen($this->file, "a+");
        // change output stream
        $this->_outputStream = null;
        $this->outputStream($STDOUT);
        \restore_exception_handler();
        return;
    }

    public function close()
    {
        \fclose($this->outputStream());
    }

    public function write($msg)
    {
        $stream = static::outputStream();
        if (!$stream) {
            return false;
        }
        if ($this->_outputDecorated) {
            $line = "\033[1A\n\033[K";
            $red = "\033[31m";
            $white = "\033[47;30m";
            $yellow = "\033[33m";
            $green = "\033[32;40m";
            $end = "\033[0m";
            $msg = \str_replace(array('<n>', '<r>', '<y>', '<w>', '<g>'), array($line, $red, $yellow, $white, $green), $msg);
            $msg = \str_replace(array('</n>', '</r>', '</y>', '</w>', '</g>'), $end, $msg);
        } else {
            $msg = \str_replace(array('<n>', '<r>', '<y>', '<w>', '<g>', '</n>', '</r>', '</y>', '</w>', '</g>'), '', $msg);
        }
        \fwrite($stream, $msg);
        \fflush($stream);
        return true;
    }

    public function writeln($msg)
    {
        $this->write($msg . PHP_EOL);
    }

    public function info($msg)
    {
        $this->writeln($this->decoratorMessage($msg, 'INFO'));
    }

    public function error($msg)
    {
        $this->writeln($this->decoratorMessage($msg, 'ERROR'));
    }

    public function debug($msg)
    {
        $this->writeln($this->decoratorMessage($msg, 'DEBUG'));
    }

    public function trace($msg)
    {
        $this->writeln($this->decoratorMessage($msg, 'TRACE'));
    }

    public function warning($msg)
    {
        $this->writeln($this->decoratorMessage($msg, 'WARNING'));
    }

    protected function decoratorMessage($msg, $type = 'INFO')
    {
        $time = date('Y-m-d H:i:s');
        switch ($type) {
            case 'INFO':
            case 'DEBUG':
                $msg = "<g>[{$type}]</g> - [{$time}] {$msg}";
                break;
            case 'WARNING':
            case 'TRACE':
                $msg = "<y>[{$type}]</y> - [{$time}] {$msg}";
                break;
            case 'ERROR':
                $msg = "<r>[{$type}]</r> - [{$time}] {$msg}";
                break;
        }
        return $msg;
    }

    protected function outputStream($stream = null)
    {
        if (!$stream) {
            $stream = $this->_outputStream ? $this->_outputStream : \STDOUT;
        }
        if (!$stream || !\is_resource($stream) || 'stream' !== \get_resource_type($stream)) {
            return false;
        }
        $stat = \fstat($stream);
        if (!$stat) {
            return false;
        }
        if (($stat['mode'] & 0170000) === 0100000) {
            // file
            $this->_outputDecorated = false;
        } else {
            $this->_outputDecorated = \function_exists('posix_isatty') && \posix_isatty($stream);
        }
        return $this->_outputStream = $stream;
    }
}