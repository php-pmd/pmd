<?php

namespace PhpPmd\Pmd\Core\File;

class ProcessFile implements FileInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
        if (!file_exists($this->file)) \touch($this->file);
    }

    public function getContent()
    {
        // TODO: Implement getContent() method.
    }

    public function setContent(string $content)
    {
        // TODO: Implement setContent() method.
    }
}