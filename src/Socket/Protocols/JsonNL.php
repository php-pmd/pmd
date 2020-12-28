<?php

namespace PhpPmd\Pmd\Socket\Protocols;
class JsonNL
{
    /**
     * @param array $buffer
     * @return string
     */
    public static function encode(array $buffer)
    {
        return json_encode($buffer);
    }

    /**
     * @param string $buffer
     * @return array
     */
    public static function decode($buffer)
    {
        return json_decode(trim($buffer), true);
    }
}