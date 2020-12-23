<?php

namespace PhpPmd\Pmd\Core\Log;
interface  LoggerInterface
{
    public function write($msg);

    public function writeln($msg);

}