<?php declare(strict_types=1);

namespace Becklyn\Messaging\MessageQueue\Infrastructure\Connection\Redis\Commands;

use Predis\Command\Command;

/**
 * @author Samuel Oechsler <so@becklyn.com>
 *
 * @since  2021-11-19
 */
class XreadGroupCommand extends Command
{
    public function getId() : string
    {
        return "XREADGROUP";
    }
}
