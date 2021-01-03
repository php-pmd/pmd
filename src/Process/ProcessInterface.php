<?php

namespace PhpPmd\Pmd\Process;
interface ProcessInterface
{
    public function list();

    public function create($name, array $config);

    public function start($name);

    public function add($config);

    public function restart($name);

    public function restartAll();

    public function stop($name);

    public function stopAll();

    public function delete($name);

    public function clearLog($name);

    public function tail($name);

}