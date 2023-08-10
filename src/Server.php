<?php

namespace level23\React\Smpp;

use level23\React\Smpp\Pdu\SubmitSm;
use level23\React\Smpp\Pdu\EnquireLink;
use level23\React\Smpp\Pdu\EnquireLinkResp;
use level23\React\Smpp\Pdu\Factory;
use level23\React\Smpp\Pdu\Unbind;
use level23\React\Smpp\Pdu\UnbindResp;
use level23\React\Smpp\Proto\CommandStatus;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use React\Socket\ServerInterface;

class Server implements ServerInterface
{
    /**
     * @var SocketServer
     */
    private $socketServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SocketServer $socketServer, LoggerInterface $logger)
    {
        $this->socketServer = $socketServer;
        $this->logger = $logger;

        $this->socketServer->on('connection', function (ConnectionInterface $conn) {
            $this->logger->debug('[smpp][' . $conn->getRemoteAddress() . '] connected');

            $connection = new Connection($conn, new Factory(), $this->logger);

            // TODO start timer for enquire_link. if exceeded - close connection

            $connection->on(EnquireLink::class, function (EnquireLink $pdu) use ($connection) {
                $this->logger->debug('[smpp][' . $connection->getRemoteAddress() . '] enquire_link');

                $response = new EnquireLinkResp();
                $response->setCommandStatus(CommandStatus::ESME_ROK);
                $response->setSequenceNumber($pdu->getSequenceNumber());
                $connection->replyWith($response);
            });

            $connection->on(SubmitSm::class, function(SubmitSm $pdu) use ($connection) {
                $this->logger->debug(
                    '[smpp][' . $connection->getRemoteAddress() . '] submit_sm '
                    . ($pdu->getSourceAddress()?->getValue() ?? 'unknown') . ' => '
                    . $pdu->getDestinationAddress()->getValue()
                    . ' (' . strlen($pdu->getShortMessage()) . ' bytes)'
                );
            });

            $connection->on(Unbind::class, function(Unbind $pdu) use ($connection) {
                $this->logger->debug('[smpp][' . $connection->getRemoteAddress() . '] unbind');

                $response = new UnbindResp();
                $response->setCommandStatus(CommandStatus::ESME_ROK);
                $response->setSequenceNumber($pdu->getSequenceNumber());
                $connection->replyWith($response);

                Loop::addTimer(0.5, function() use ($connection) {
                    $this->logger->debug('[smpp][' . $connection->getRemoteAddress() . '] disconnecting');
                    $connection->close();
                });
            });

            $connection->on('close', function() use ($connection) {
                $this->logger->debug('[smpp][' . $connection->getRemoteAddress() . '] disconnected');
            });

            $this->socketServer->emit(Connection::class, [$connection]);
        });
    }

    public function getAddress()
    {
        return $this->socketServer->getAddress();
    }

    public function pause()
    {
        $this->socketServer->pause();
    }

    public function resume()
    {
        $this->socketServer->resume();
    }

    public function close()
    {
        $this->socketServer->close();
    }

    public function on($event, callable $listener)
    {
        $this->socketServer->on($event, $listener);
    }

    public function once($event, callable $listener)
    {
        $this->socketServer->once($event, $listener);
    }

    public function removeListener($event, callable $listener)
    {
        $this->socketServer->removeListener($event, $listener);
    }

    public function removeAllListeners($event = null)
    {
        $this->socketServer->removeAllListeners($event);
    }

    public function listeners($event = null)
    {
        $this->socketServer->listeners($event);
    }

    public function emit($event, array $arguments = [])
    {
        $this->socketServer->emit($event, $arguments);
    }
}
