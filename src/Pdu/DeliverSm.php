<?php

namespace level23\React\Smpp\Pdu;

use Carbon\Carbon;
use level23\React\Smpp\Proto\DateTime;
use \level23\React\Smpp\Utils\DataWrapper;

class DeliverSm extends SubmitSm
{
    private string $messageId;
    private string $submitted;
    private string $delivered;
    private string $submitDate;
    private string $doneDate;
    private string $stat;
    private string $err;
    private string $text;

    private bool $isDeliveryReceipt = false;

    public function isDeliveryReceipt(): bool
    {
        return $this->isDeliveryReceipt;
    }

    public function setIsDeliveryReceipt(bool $isDeliveryReceipt): self
    {
        $this->isDeliveryReceipt = $isDeliveryReceipt;

        return $this;
    }

    public function getDeliveryReceiptMessageId(): string
    {
        return $this->messageId;
    }

    public function setDeliveryReceiptMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getDeliveryReceiptSubmitted(): string
    {
        return $this->submitted;
    }

    public function setDeliveryReceiptSubmitted(int $submitted): self
    {
        if ($submitted < 0 || $submitted > 999 ) {
            throw new \OutOfRangeException($submitted . ' not in range [000-999]');
        }

        $this->submitted = str_pad((string)$submitted, 3, '0', STR_PAD_LEFT);

        return $this;
    }

    public function getDeliveryReceiptDelivered(): string
    {
        return $this->delivered;
    }

    public function setDeliveryReceiptDelivered(int $delivered): self
    {
        if ($delivered < 0 || $delivered > 999 ) {
            throw new \OutOfRangeException($delivered . ' not in range [000-999]');
        }

        $this->delivered = str_pad((string)$delivered, 3, '0', STR_PAD_LEFT);

        return $this;
    }

    public function getDeliveryReceiptSubmitDate(): string
    {
        return $this->submitDate;
    }

    public function setDeliveryReceiptSubmitDate(Carbon $submitDate): self
    {
        $this->submitDate = $submitDate->format('YmdHis');

        return $this;
    }

    public function getDeliveryReceiptDoneDate(): string
    {
        return $this->doneDate;
    }

    public function setDeliveryReceiptDoneDate(Carbon $submitDate): self
    {
        $this->doneDate = $submitDate->format('YmdHis');

        return $this;
    }

    public function getDeliveryReceiptStat(): string
    {
        return $this->stat;
    }

    public function setDeliveryReceiptStat(string $messageState): self
    {
        $this->stat = $messageState;

        return $this;
    }

    public function getDeliveryReceiptErr(): string
    {
        return $this->err;
    }

    public function setDeliveryReceiptErr(string $err): self
    {
        $this->err = $err;

        return $this;
    }

    public function getDeliveryReceiptText(): string
    {
        return $this->text;
    }

    public function setDeliveryReceiptText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCommandId(): int
    {
        return 0x00000005;
    }

    public function __toString(): string
    {
        if ($this->isDeliveryReceipt()) {
            $shortMessage = sprintf(
                "id:%s sub:%s dlvrd:%s submit date:%s done date:%s stat:%s err:%s text:%s",
                $this->getDeliveryReceiptMessageId(),
                $this->getDeliveryReceiptSubmitted(),
                $this->getDeliveryReceiptDelivered(),
                $this->getDeliveryReceiptSubmitDate(),
                $this->getDeliveryReceiptDoneDate(),
                $this->getDeliveryReceiptStat(),
                $this->getDeliveryReceiptErr(),
                ''
            );

            $this->setShortMessage($shortMessage);

            $wrapper = new DataWrapper('');
            $wrapper->writeNullTerminatedString(
                $this->getServiceType()
            )->writeAddress(
                $this->getSourceAddress()
            )->writeAddress(
                $this->getDestinationAddress()
            )->writeInt8(
                $this->getEsmClass()
            )->writeInt8(
                $this->getProtocolId()
            )->writeInt8(
                $this->getPriorityFlag()
            )->writeNullTerminatedString(
                $this->getScheduleDeliveryTime()
                    ? (new DateTime($this->getScheduleDeliveryTime()->format('c')))->__toString()
                    : ''
            )->writeNullTerminatedString(
                $this->getValidityPeriod()
                    ? (new DateTime($this->getValidityPeriod()->format('c')))->__toString()
                    : ''
            )->writeInt8(
                $this->getRegisteredDelivery()
            )->writeInt8(
                $this->getReplaceIfPresentFlag()
            )->writeInt8(
                $this->getDataCoding()
            )->writeInt8(
                $this->getSmDefaultMsgId()
            )->writeInt8(
                strlen($this->getShortMessage())
            )->writeBytes(
                $this->getShortMessage()
            )->writeInt8( // Write 0x01E (receipt message id, 2 bytes)
                0
            )->writeInt8(
                0x1E
            )->writeInt8( // Write length (also 2 bytes)
                0
            )->writeInt8(
                strlen($this->getDeliveryReceiptMessageId())
            )->writeBytes( // The message
                $this->getDeliveryReceiptMessageId()
            )->writeInt8( // Write 0x0427 (receipt message state)
                0x04
            )->writeInt8(
                0x27
            )->writeInt8( // message state length (2 bytes, 0x0001)
                0x00
            )->writeInt8(
                0x01
            )->writeInt8( // message state (0x02 = delivered)
                0x02
            );

            $this->setBody($wrapper->__toString());

            return pack(
                    'NNNN',
                    $this->getCommandLength(),
                    $this->getCommandId(),
                    $this->getCommandStatus(),
                    $this->getSequenceNumber()
                ) . $this->getBody();
        }

        return parent::__toString();
    }
}
