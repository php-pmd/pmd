<?php

namespace PhpPmd\Pmd\Core\Log;

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

    public function dump()
    {
        global $STDOUT, $STDERR;
        \set_error_handler(function () {
        });
        \fclose(\STDOUT);
        \fclose(\STDERR);
        $STDOUT = \fopen($this->file, "a+");
        $STDERR = \fopen($this->file, "a+");
        // change output stream
        $this->_outputStream = null;
        $this->outputStream($STDOUT);
        \restore_error_handler();
        return;
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
        }
        \fwrite($stream, $msg);
        \fflush($stream);
        return true;
    }

    public function writeln($msg)
    {
        $this->write($msg . PHP_EOL);
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