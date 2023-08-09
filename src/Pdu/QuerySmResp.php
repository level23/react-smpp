<?php

namespace level23\React\Smpp\Pdu;

class QuerySmResp extends Pdu
{
    public function getCommandId(): int
    {
        return 0x80000003;
    }
}
