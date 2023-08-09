<?php

namespace level23\React\Smpp\Pdu;

class EnquireLink extends Pdu
{
    public function getCommandId(): int
    {
        return 0x00000015;
    }
}
