<?php

namespace PhpPmd\Pmd\Http\Business;

class ProcessBusiness extends SocketBusiness
{
    public function getList($callback)
    {
        $cmd = ['cmd' => 'setting'];
        return $this->send('127.0.0.1:2022', $cmd, $callback);
    }
}