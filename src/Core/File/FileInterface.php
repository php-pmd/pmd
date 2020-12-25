<?php

namespace PhpPmd\Pmd\Core\File;

interface FileInterface
{
    public function __construct(string $file);

    public function getContent();

    public function setContent($content);
}