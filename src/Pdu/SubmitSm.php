<?php

namespace alexeevdv\React\Smpp\Pdu;

use alexeevdv\React\Smpp\Proto\Address;
use alexeevdv\React\Smpp\Proto\Contract\Address as AddressContract;
use alexeevdv\React\Smpp\Proto\DateTime;
use alexeevdv\React\Smpp\Utils\DataWrapper;
use DateTimeInterface;

class SubmitSm extends Pdu implements Contract\SubmitSm
{
    /**
     * @var string
     */
    private $serviceType;

    /**
     * @var AddressContract
     */
    private $sourceAddress;

    /**
     * @var AddressContract
     */
    private $destinationAddress;

    /**
     * @var int
     */
    private $dataCoding;

    /**
     * @var string
     */
    private $shortMessage;

    /**
     * @var int
     */
    private $esmClass;

    /**
     * @var DateTimeInterface
     */
    private $validityPeriod;

    /**
     * @var DateTimeInterface
     */
    private $scheduleDeliveryTime;

    public function __construct(int $status = 0, int $sequence = 1, $body = '')
    {
        parent::__construct($status, $sequence, $body);

        if (strlen($body) === 0) {
            return;
        }

        $wrapper = new DataWrapper($body);
        $this->setServiceType(
            $wrapper->readNullTerminatedString(6)
        );
        $this->setSourceAddress(new Address(
            $wrapper->readInt8(),
            $wrapper->readInt8(),
            $wrapper->readNullTerminatedString(21)
        ));
        $this->setDestinationAddress(new Address(
            $wrapper->readInt8(),
            $wrapper->readInt8(),
            $wrapper->readNullTerminatedString(21)
        ));
        $this->setEsmClass(
            $wrapper->readInt8()
        );

        // protocol_id int8
        $wrapper->readInt8();

        // priority_flag int8
        $wrapper->readInt8();

        $scheduleDeliveryTime = $wrapper->readNullTerminatedString(17);
        if (strlen($scheduleDeliveryTime)) {
            $this->setScheduleDeliveryTime(new DateTime($scheduleDeliveryTime));
        }

        $validityPeriod = $wrapper->readNullTerminatedString(17);
        if (strlen($validityPeriod)) {
            $this->setValidityPeriod(new DateTime($validityPeriod));
        }

        // registered_delivery int8
        $wrapper->readInt8();

        // replace_if_present_flag int8
        $wrapper->readInt8();

        $this->setDataCoding(
            $wrapper->readInt8()
        );

        // sm_default_msg_id int8
        $wrapper->readInt8();

        $smLength = $wrapper->readInt8();
        $this->setShortMessage(
            $wrapper->readBytes($smLength)
        );

        /* Body layout
        optional

        user_message_reference TLV
        source_port TLV
        source_addr_subunit TLV
        destination_port TLV
        dest_addr_subunit TLV
        sar_msg_ref_num TLV
        sar_total_segments TLV
        sar_segment_seqnum TLV
        more_messages_to_send TLV
        payload_type TLV
        message_payload TLV
        privacy_indicator TLV
        callback_num TLV
        callback_num_pres_ind TLV
        callback_num_atag TLV
        source_subaddress TLV
        dest_subaddress TLV
        user_response_code TLV
        display_time TLV
        sms_signal TLV
        ms_validity TLV
        ms_msg_wait_facilities TLV
        number_of_messages TLV
        alert_on_msg_delivery TLV
        language_indicator TLV
        its_reply_type TLV
        its_session_info TLV
        ussd_service_op TLV

        */

    }

    public function getCommandId(): int
    {
        return 0x00000004;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    public function setServiceType(string $serviceType): self
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    public function getSourceAddress(): AddressContract
    {
        return $this->sourceAddress;
    }

    public function setSourceAddress(AddressContract $address): self
    {
        $this->sourceAddress = $address;
        return $this;
    }

    public function getDestinationAddress(): AddressContract
    {
        return $this->destinationAddress;
    }

    public function setDestinationAddress(AddressContract $address): self
    {
        $this->destinationAddress = $address;
        return $this;
    }

    public function getEsmClass(): int
    {
        return $this->esmClass;
    }

    public function setEsmClass(int $esmClass): self
    {
        $this->esmClass = $esmClass;
        return $this;
    }

    public function getDataCoding(): int
    {
        return $this->dataCoding;
    }

    public function setDataCoding(int $dataCoding): self
    {
        $this->dataCoding = $dataCoding;
        return $this;
    }

    public function getShortMessage(): string
    {
        return $this->shortMessage;
    }

    public function setShortMessage(string $shortMessage): self
    {
        $this->shortMessage = $shortMessage;
        return $this;
    }

    public function getValidityPeriod(): ?DateTimeInterface
    {
        return $this->validityPeriod;
    }

    public function setValidityPeriod(?DateTimeInterface $validityPeriod): self
    {
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    public function getScheduleDeliveryTime(): ?DateTimeInterface
    {
        return $this->scheduleDeliveryTime;
    }

    public function setScheduleDeliveryTime(?DateTimeInterface $scheduleDeliveryTime): self
    {
        $this->scheduleDeliveryTime = $scheduleDeliveryTime;
        return $this;
    }

    public function __toString(): string
    {
        $wrapper = new DataWrapper('');
        $wrapper->writeNullTerminatedString(
            $this->getServiceType()
        )->writeInt8(
            $this->getSourceAddress()->getTon()
        )->writeInt8(
            $this->getSourceAddress()->getNpi()
        )->writeNullTerminatedString(
            $this->getSourceAddress()->getValue()
        )->writeInt8(
            $this->getDestinationAddress()->getTon()
        )->writeInt8(
            $this->getDestinationAddress()->getNpi()
        )->writeNullTerminatedString(
            $this->getDestinationAddress()->getValue()
        )->writeInt8(
            $this->getEsmClass()
        )->writeInt8(
            // protocol_id int8
            0
        )->writeInt8(
            // priority_flag int8
            0
        )->writeNullTerminatedString(
            $this->getScheduleDeliveryTime()
            ? (new DateTime($this->getScheduleDeliveryTime()->format('c')))->__toString()
            : ''
        )->writeNullTerminatedString(
            $this->getValidityPeriod()
            ? (new DateTime($this->getValidityPeriod()->format('c')))->__toString()
            : ''
        )->writeInt8(
            // registered_delivery int8
            0
        )->writeInt8(
            // replace_if_present_flag int8
            0
        )->writeInt8(
            $this->getDataCoding()
        )->writeInt8(
            // sm_default_msg_id int8
            0
        )->writeInt8(
            strlen($this->getShortMessage())
        )->writeBytes(
            $this->getShortMessage()
        );

        $this->setBody($wrapper->__toString());

        return parent::__toString();
    }
}
