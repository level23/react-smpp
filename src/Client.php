<?php

namespace level23\React\Smpp;

use Psr\Log\LoggerInterface;
use level23\React\Smpp\Pdu\EnquireLink;
use level23\React\Smpp\Pdu\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class Client implements ConnectorInterface
{
    private const ENQUIRE_LINK_INTERVAL = 28;

    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ConnectorInterface $connector, LoopInterface $loop, LoggerInterface $logger)
    {
        $this->connector = $connector;
        $this->loop = $loop;
        $this->logger = $logger;
    }

    public function connect($uri)
    {
        return $this->connector->connect($uri)->then(function (ConnectionInterface $conn) {
            $connection = new Connection($conn, new Factory(), $this->logger);

            $enquireLinkTimer = $this->loop->addPeriodicTimer(
                self::ENQUIRE_LINK_INTERVAL,
                function () use ($connection) {
                    $enquireLink = new EnquireLink();
                    $connection->send($enquireLink)->otherwise(function () use ($connection) {
                        // TODO throw exception instead?
                        $connection->close();
                    });
                }
            );

            $connection->on('close', function () use ($enquireLinkTimer) {
                $this->loop->cancelTimer($enquireLinkTimer);
            });

            $deferred = new Deferred();
            $deferred->resolve($connection);
            return $deferred->promise();
        });
    }
}
