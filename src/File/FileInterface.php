<?php

namespace PhpPmd\Pmd\File;

interface FileInterface
{
    public function __construct(string $file);

    public function getContent();

    public function setContent($content);
}