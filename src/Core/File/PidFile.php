<?php

namespace PhpPmd\Pmd\Core\File;

class PidFile extends AbstractFile
{
    public function getContent()
    {
        $pid = \file_get_contents($this->file);
        return $pid == '' ? 0 : $pid;
    }

    public function setContent($content)
    {
        @file_put_contents($this->file, $content);
    }

    public function isRunning()
    {
        $pid = $this->getContent();
        return $pid && \posix_kill($pid, 0);
    }

    public function unlink()
    {
        @unlink($this->file);
    }
}