<?php

namespace level23\React\Smpp\Pdu;

class BindTransmitter extends Bind
{
    public function getCommandId(): int
    {
        return 0x00000002;
    }
}
