<?php

namespace PhpPmd\Pmd\Http\Business;

class ProcessBusiness extends SocketBusiness
{
    public function getList($address, $callback)
    {
        $cmd = ['cmd' => 'process_list'];
        return $this->send($address, $cmd, $callback);
    }

    public function start($address, $name, $callback)
    {
        $cmd = ['cmd' => 'start', 'data' => ['name' => $name]];
        return $this->send($address, $cmd, $callback);
    }
}