<?php

namespace PhpPmd\Pmd\Core\File;

use Symfony\Component\Yaml\Yaml;

class ConfigFile implements FileInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
        if (!file_exists($this->file)) \touch($this->file);
    }

    public function getContent()
    {
        return Yaml::parse(@file_get_contents($this->file)) ?? [];
    }

    public function setContent($content)
    {
        @file_put_contents($this->file, Yaml::dump($content));
    }
}