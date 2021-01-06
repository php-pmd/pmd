<?php

namespace PhpPmd\Pmd\File;

interface FileInterface
{
    public function __construct($file);

    public function getContent();

    public function setContent($content);
}