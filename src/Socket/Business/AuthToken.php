<?php

namespace PhpPmd\Pmd\Socket\Business;

class AuthToken extends BaseBusiness
{
    public function __invoke($data)
    {
        $config = \configFile()->getContent();
        $socket = $config['socket'];
        return $data['app_key'] == $socket['app_key'] && $data['app_secret'] == $socket['app_secret'];
    }
}