<?php

namespace PhpPmd\Pmd\File;

use Symfony\Component\Yaml\Yaml;

class ConfigFile extends AbstractFile
{

    public function getContent()
    {
        return Yaml::parse(@file_get_contents($this->file)) ?? [];
    }

    public function setContent($content)
    {
        @file_put_contents($this->file, Yaml::dump($content));
    }
}