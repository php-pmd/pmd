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

    public function restart($address, $name, $callback)
    {
        $cmd = ['cmd' => 'restart', 'data' => ['name' => $name]];
        return $this->send($address, $cmd, $callback);
    }

    public function stop($address, $name, $callback)
    {
        $cmd = ['cmd' => 'stop', 'data' => ['name' => $name]];
        return $this->send($address, $cmd, $callback);
    }

    public function add($address, $config, $callback)
    {
        $cmd = ['cmd' => 'add', 'data' => $config];
        return $this->send($address, $cmd, $callback);
    }

    public function delete($address, $name, $callback)
    {
        $cmd = ['cmd' => 'delete', 'data' => ['name' => $name]];
        return $this->send($address, $cmd, $callback);
    }

    public function stopall($address, $callback)
    {
        $cmd = ['cmd' => 'stopall'];
        return $this->send($address, $cmd, $callback);
    }


    public function restartall($address, $callback)
    {
        $cmd = ['cmd' => 'restartall'];
        return $this->send($address, $cmd, $callback);
    }

}