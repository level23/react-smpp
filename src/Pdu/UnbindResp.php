<?php

namespace level23\React\Smpp\Pdu;

class UnbindResp extends Pdu
{
    public function getCommandId(): int
    {
        return 0x80000006;
    }
}
