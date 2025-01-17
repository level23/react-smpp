<?php

use Carbon\Carbon;
use level23\React\Smpp\Connection;
use level23\React\Smpp\Pdu\BindTransceiver;
use level23\React\Smpp\Pdu\BindTransceiverResp;
use level23\React\Smpp\Pdu\DeliverSm;
use level23\React\Smpp\Pdu\SubmitSm;
use level23\React\Smpp\Pdu\SubmitSmResp;
use level23\React\Smpp\Proto\CommandStatus;
use level23\React\Smpp\Proto\MessageState;
use level23\React\Smpp\Server;
use Firehed\SimpleLogger\Stdout;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$logger = new Stdout();

$socketServer = new SocketServer('tcp://0.0.0.0:2775');
$smppServer   = new Server($socketServer, $logger);

$smppServer->on(Connection::class, static function (Connection $connection) use ($logger) {
    $connection->on(BindTransceiver::class, static function (BindTransceiver $pdu) use ($connection, $logger) {
        $logger->info('bind_transceiver 1. system_id: {systemId}, password: {password}', [
            'systemId' => $pdu->getSystemId(),
            'password' => $pdu->getPassword(),
        ]);

        $response = new BindTransceiverResp();
        $response->setCommandStatus(CommandStatus::ESME_ROK);
        $response->setSystemId($pdu->getSystemId());
        $connection->replyWith($response);
    });

    $connection->on(SubmitSm::class, static function (SubmitSm $pdu) use ($connection, $logger) {
        $logger->info('sumbit_sm. source: {source}, destination: {destination}, short_message: {shortMessage}', [
            'source'       => $pdu->getSourceAddress() !== null ? $pdu->getSourceAddress()->getValue() : null,
            'destination'  => $pdu->getDestinationAddress()->getValue(),
            'shortMessage' => $pdu->getShortMessage(),
        ]);

        $messageId  = uniqid('', true);
        $submitDate = Carbon::now();

        $response = new SubmitSmResp();
        $response->setSequenceNumber($pdu->getSequenceNumber());
        $response->setCommandStatus(CommandStatus::ESME_ROK);
        $response->setMessageId($messageId);
        $connection->replyWith($response);

        if ($pdu->getRegisteredDelivery()) {
            $logger->info('Queuing delivery response.');

            // Simulate delivery
            Loop::addTimer(2, function () use ($submitDate, $connection, $pdu, $messageId) {
                $response = new DeliverSm();
                $response->setIsDeliveryReceipt(true);
                $response->setServiceType('OMV4');
                $response->setSourceAddress($pdu->getDestinationAddress());
                $response->setDestinationAddress($pdu->getSourceAddress());
                $response->setEsmClass(0x04);
                $response->setDeliveryReceiptMessageId($messageId);
                $response->setDeliveryReceiptSubmitted(1);
                $response->setDeliveryReceiptDelivered(1);
                $response->setDeliveryReceiptSubmitDate($submitDate);
                $response->setDeliveryReceiptDoneDate($submitDate->addSeconds(2));
                $response->setDeliveryReceiptStat(MessageState::DELIVERED);
                $response->setDeliveryReceiptErr('000');
                $response->setDeliveryReceiptText($pdu->getShortMessage());

                $connection->replyWith($response);
            });
        }
    });

    $connection->on('error', static function (Throwable $e) use ($connection, $logger) {
        $logger->error($e->getMessage(), ['exception' => $e]);
        $connection->close();
    });
});

Loop::run();
