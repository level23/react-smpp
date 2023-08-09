<?php

namespace level23\React\Smpp\Pdu;

class CancelSmResp extends Pdu
{
    public function getCommandId(): int
    {
        return 0x00000008;
    }
}
