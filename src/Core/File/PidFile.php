<?php

namespace PhpPmd\Pmd\Core\File;

class PidFile implements FileInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
        if (!file_exists($this->file)) \touch($this->file);
    }

    public function getContent()
    {
        return \is_file($this->file) ? \file_get_contents($this->file) : 0;
    }

    public function setContent(string $content)
    {
        @file_put_contents($this->file, $content);
    }

    public function exists()
    {
        return $this->getContent();
    }

    public function unlink()
    {
        @unlink($this->file);
    }
}