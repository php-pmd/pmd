<?php

namespace PhpPmd\Pmd\Core\Process;
interface ProcessInterface
{
    public function list();

    public function create(array $config);

    public function get(string $name);

    public function restart(string $name);

    public function restartAll();

    public function stop(string $name);

    public function stopAll();

    public function delete(string $name);

    public function clearLog(string $name);

    public function log(string $name);

}