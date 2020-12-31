<?php

namespace PhpPmd\Pmd\Socket\Business;

class CreateToken extends BaseBusiness
{
    public function __invoke($data)
    {
        $config = \configFile()->getContent();
        $socket = $config['socket'];
        if ($socket['app_key'] == $data['app_key'] && $socket['app_secret'] == $data['app_secret']) {
            $token = hash('sha512', $socket['app_key'] . $socket['app_secret'] . $this->connection->getRemoteAddress());
            return ["link_state" => 1, 'access_token' => $token];
        } else {
            return ["link_state" => 0];
        }
    }
}