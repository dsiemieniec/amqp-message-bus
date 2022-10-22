<?php

namespace Siemieniec\AsyncCommandBus\Tests\Serializer;

use Siemieniec\AsyncCommandBus\Command\Properties\CommandProperties;
use App\Command\SimpleCommand;
use Siemieniec\AsyncCommandBus\Serializer\DefaultCommandSerializer;
use PHPUnit\Framework\TestCase;

class CommandSerializerTest extends TestCase
{
    public function testCanSerializeSimpleCommand(): void
    {
        $serializer = new DefaultCommandSerializer();
        $command = new SimpleCommand(1, 'test');

        self::assertEquals(
            $command,
            $serializer->deserialize(
                $serializer->serialize($command, new CommandProperties())
            )
        );
    }
}
