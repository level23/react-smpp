<?php

namespace level23\React\Smpp\Proto;

interface MessageState
{
    public const DELIVERED     = 'DELIVRD'; // Delivery has not yet been initiated
    public const EXPIRED       = 'EXPIRED'; // Message validity period has expired
    public const DELETED       = 'DELETED'; // The message has been cancelled or deleted from the MC
    public const UNDELIVERABLE = 'UNDELIV'; // The message has encountered a delivery error and is deemed permanently undeliverable
    public const ACCEPTED      = 'ACCEPTD'; // The message has encountered a delivery error and is deemed permanently undeliverable
    public const UNKNOWN       = 'UNKNOWN'; // The message has encountered a delivery error and is deemed permanently undeliverable
    public const REJECTED      = 'REJECTD'; // The message has encountered a delivery error and is deemed permanently undeliverable
}
