<?php

namespace PhpPmd\Pmd\Core\Http;

use PhpPmd\Pmd\Core\Http\Response\HtmlResponse;

class Template
{
    private $templatePath = __DIR__ . '/view/';

    /**
     * @param $template
     * @return string
     */
    public function display($template, $data)
    {
        if (file_exists($this->templatePath . $template)) {
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