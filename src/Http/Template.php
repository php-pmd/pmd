<?php

namespace PhpPmd\Pmd\Http;

use PhpPmd\Pmd\Http\Response\HtmlResponse;

class Template
{
    protected $templatePath;

    protected $data;

    public function __construct($templatePath, $data)
    {
        $this->templatePath = $templatePath;
        $this->data = $data;
    }

    /**
     * @param $template
     * @return string
     */
    public function display($template, $data)
    {
        if (file_exists($this->templatePath . $template)) {
            $data = array_merge($this->data, $data);
            if (!empty($data)) extract($data);
            ob_start();
            include($this->templatePath . $template);
            $res = ob_get_contents();
            ob_end_clean();
            return HtmlResponse::ok($res);
        } else {
            $msg = 'error: \'' . $this->templatePath . $template . '\' template does not exist.';
            trigger_error($msg);
            return ENV == 'PRO' ? HtmlResponse::internalServerError() : HtmlResponse::internalServerError($msg);
        }
    }
}