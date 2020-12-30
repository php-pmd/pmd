<?php

namespace PhpPmd\Pmd\Process;
interface ProcessInterface
{
    public function list();

    public function create($name, array $config);

    public function get(string $name);

    public function start($name);

    public function restart(string $name);

    public function restartAll();

    public function stop(string $name);

    public function stopAll();

    public function delete(string $name);

    public function clearLog(string $name);

    public function log(string $name);

}