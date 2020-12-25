<?php


namespace PhpPmd\Pmd\Core\Http\Controller;


class BaseController
{
    public function view($template, $data = [])
    {
        return view()->display($template, $data);
    }
}