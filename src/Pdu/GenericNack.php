<?php

namespace level23\React\Smpp\Pdu;

class GenericNack extends Pdu
{
    public function getCommandId(): int
    {
        return 0x80000000;
    }
}
