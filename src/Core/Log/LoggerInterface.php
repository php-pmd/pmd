<?php

namespace PhpPmd\Pmd\Core\Log;
interface  LoggerInterface
{
    public function write($msg);

    public function writeln($msg);

    public function info($msg);

    public function error($msg);

    public function debug($msg);

    public function trace($msg);

    public function warning($msg);

    public function close();
}