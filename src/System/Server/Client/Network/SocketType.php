<?php

declare(strict_types=1);

namespace App\System\Server\Client\Network;

use App\System\Kernel;
use App\System\Server\Client\AbstractClient;
use App\System\Server\Client\CliClient;
use App\System\Server\Client\MainClient;
use App\System\Server\Client\UiMessageReceiverClient;
use App\System\Server\Client\UnblockingCliClient;

enum SocketType: string
{
    case MAIN = 'main';
    case CLI = 'cli';
    case UNBLOCKING_CLI = 'unblocking_cli';

    case UI_MESSAGE_RECEIVER = 'ui_message_receiver';

    public function getClient(): ?AbstractClient
    {
        return match($this) {
            self::MAIN => Kernel::getContainer()->get(MainClient::class),
            self::CLI => Kernel::getContainer()->get(CliClient::class),
            self::UNBLOCKING_CLI => Kernel::getContainer()->get(UnblockingCliClient::class),
            self::UI_MESSAGE_RECEIVER => Kernel::getContainer()->get(UiMessageReceiverClient::class),
        };
    }
}
