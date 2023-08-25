<?php

declare(strict_types=1);

namespace App\System\Server;

trait PacketTrait
{
    public const PACKET_SEPARATOR = "\u{1f}";
    public function pack(?string $data = null): string
    {
        $preparedData = $data ? sprintf(' %s', $data) : '';
        return sprintf('%s%s%s', $this->value, $preparedData, self::PACKET_SEPARATOR);
    }

    static public function getPackets(?string $rawData): array
    {
        $rawData = $rawData ?? '';
        return array_filter(explode(self::PACKET_SEPARATOR, $rawData));
    }
}
