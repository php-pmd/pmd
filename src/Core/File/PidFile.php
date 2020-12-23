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
        $pid = \file_get_contents($this->file);
        return $pid == '' ? 0 : $pid;
    }

    public function setContent(string $content)
    {
        @file_put_contents($this->file, $content);
    }

    public function exists()
    {
        return $this->getContent() ? true : false;
    }

    public function unlink()
    {
        @unlink($this->file);
    }
}