<?php

declare(strict_types=1);

namespace App\System\Server;

use App\System\PresetLibrary\AbstractPreset;
use App\System\PresetLibrary\PresetDataType;

class ServerPreset extends AbstractPreset
{
    private string $host = '127.0.0.1';
    private string $port = '1988';

    public function __construct(string $name, private readonly string $type)
    {
        parent::__construct(PresetDataType::SERVER_CONFIG, $name);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function setPort(string $port): self
    {
        $this->port = $port;
        return $this;
    }
}
