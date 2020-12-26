<?php

namespace PhpPmd\Pmd\Core\File;

abstract class AbstractFile implements FileInterface
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
        if (!file_exists($this->file)) \touch($this->file);
    }
}