<?php

namespace level23\React\Smpp\Pdu;

class QuerySm extends Pdu
{
    public function getCommandId(): int
    {
        return 0x00000003;
    }
}
