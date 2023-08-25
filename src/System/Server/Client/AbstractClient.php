<?php

declare(strict_types=1);

namespace App\System\Server\Client;

use Amp\Socket\ClientTlsContext;
use Amp\Socket\ConnectContext;
use Amp\Socket\Socket;
use App\System\Server\Client\Network\SocketType;
use App\System\Server\ClientPacketHeader;
use App\System\Server\ServerPacketHeader;
use App\System\Server\ServerPreset;
use League\Uri\Http;
use function Amp\async;
use function Amp\delay;
use function Amp\Socket\connect;
use function Amp\Socket\connectTls;

abstract class AbstractClient
{
    protected Socket $socket;

    public function __construct(private readonly ServerPreset $serverPreset)
    {
    }

    public function init(?string $clientUuid): bool {
        $this->socket = $this->connect(
            sprintf(
                'http://%s:%s',
                $this->serverPreset->getHost(),
                $this->serverPreset->getPort(),
            )
        );

        $rawPackageData = $this->register($this->getSocketType(), $clientUuid);

        [$packageHeader, $packageData] = $this->parsePacket($rawPackageData);

        $message = implode(' ', $packageData);

        $initResult = $packageHeader === ServerPacketHeader::CLIENT_REGISTER_SUCCESS;

        echo sprintf(
            "Client Init %s: %s",
            !$initResult ? 'Failed' : 'Accepted',
            $message,
        );

        if ($initResult) {
            async(function () {
               while ($this->socket->isReadable() && $this->socket->isWritable()) {
                   delay(0.1);
               }
               die();
            });
        }

        return $initResult;
    }

    protected function register(SocketType $socketType, ?string $clientUuid): string
    {
        $this->socket->write(sprintf('%s %s %s',
            ClientPacketHeader::ATTACH_CLIENT->value,
            $clientUuid,
            $socketType->value
        ));

        return $this->socket->read();
    }

    abstract public function start(): void;
    abstract protected function getSocketType(): SocketType;

    private function connect(string $address): Socket
    {
        $uri = Http::createFromString($address);
        $host = $uri->getHost();
        $port = $uri->getPort() ?? ($uri->getScheme() === 'https' ? 443 : 80);

        $connectContext = (new ConnectContext)
            ->withTlsContext(new ClientTlsContext($host));

        return $uri->getScheme() === 'http'
            ? connect($host . ':' . $port, $connectContext)
            : connectTls($host . ':' . $port, $connectContext);
    }

    protected function parsePacket(string $rawPacketData): array
    {
        $packetData = explode(' ', $rawPacketData);
        $rawHeader = array_shift($packetData);
        $packetHeader = ServerPacketHeader::tryFrom($rawHeader);

        return [
            $packetHeader,
            $packetData
        ];
    }

    protected function printPacketInfo(ServerPacketHeader $serverPacketHeader, array $packetData): void
    {
        $message = implode(' ', $packetData);

        echo sprintf(
            "\n[%s] %s",
            $serverPacketHeader->value,
            $message
        );
    }

    protected function readSocket(): string
    {
        $buffer = '';
        do {
            $buffer .= $this->socket->read();
        } while(!str_ends_with($buffer, ServerPacketHeader::PACKET_SEPARATOR));

        return $buffer;
    }
}
