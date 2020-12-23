<?php

namespace PhpPmd\Pmd\Core\Di;

interface ContainerInterface
{
    public function __construct($definitions = []);

    public function get($id);

    public function has($id);

    public function injection($id, $concrete);

    public function make($name);

}