<?php

namespace level23\React\Smpp\Pdu;

use level23\React\Smpp\Utils\DataWrapper;

abstract class BindResp extends Pdu
{
    /**
     * @var string
     */
    private $systemId;

    public function __construct($body = '')
    {
        parent::__construct($body);
        if (strlen($body) === null) {
            return;
        }

        $wrapper = new DataWrapper($body);
        $this->setSystemId(
            $wrapper->readNullTerminatedString(16)
        );
        /**
         * optional
         *
         * sc_interface_version TLV
         */
    }

    public function getSystemId(): string
    {
        return $this->systemId;
    }

    public function setSystemId(string $systemId): self
    {
        $this->systemId = $systemId;
        return $this;
    }

    public function __toString(): string
    {
        $wrapper = new DataWrapper('');
        $wrapper->writeNullTerminatedString($this->getSystemId());
        $this->setBody($wrapper->__toString());

        return parent::__toString();
    }
}
