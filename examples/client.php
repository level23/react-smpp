<?php

use level23\React\Smpp\Client;
use level23\React\Smpp\Connection;
use level23\React\Smpp\Pdu\BindTransmitter;
use level23\React\Smpp\Pdu\BindTransmitterResp;
use level23\React\Smpp\Pdu\DeliverSm;
use level23\React\Smpp\Pdu\DeliverSmResp;
use level23\React\Smpp\Pdu\SubmitSm;
use level23\React\Smpp\Pdu\SubmitSmResp;
use level23\React\Smpp\Proto\Address;
use level23\React\Smpp\Proto\Address\Ton;
use level23\React\Smpp\Proto\Address\Npi;
use Firehed\SimpleLogger\Stdout;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Connector;

require_once 'vendor/autoload.php';

$loop = LoopFactory::create();
$logger = new Stdout();
$connector = new Connector($loop);
$smppClient = new Client($connector, $loop);

$smppClient
    ->connect('127.0.0.1:2775')
    ->then(function (Connection $connection) use ($logger) {
        $logger->info('Connected');

        $connection->on(DeliverSm::class, function (DeliverSm $pdu) use ($connection) {
            $connection->replyWith(new DeliverSmResp());
        });

        $bindTransmitter = new BindTransmitter();
        $bindTransmitter->setSystemId('user');
        $bindTransmitter->setPassword('password');

        $connection
            ->send($bindTransmitter)
            ->then(function (BindTransmitterResp $pdu) use ($connection, $logger) {
                $logger->info('Binded');

                $submitSm = new SubmitSm();
                $submitSm->setSourceAddress(new Address(Ton::international(), Npi::isdn(), '1234567890'));
                $submitSm->setDestinationAddress(new Address(Ton::international(), Npi::isdn(), '1234567890'));
                $submitSm->setShortMessage('Hello there!');
                return $connection->send($submitSm);
            })
            ->then(function (SubmitSmResp $pdu) use ($connection, $logger) {
                $logger->info('Submited. message_id: {messageId}', [
                    'messageId' => $pdu->getMessageId(),
                ]);
                $connection->close();
            })
        ;
    })
;

$loop->run();
