<?php

namespace App\Tests\Serializer;

use App\Command\SimpleCommand;
use App\Serializer\CommandSerializer;
use PHPUnit\Framework\TestCase;

class CommandSerializerTest extends TestCase
{
    public function testCanSerializeSimpleCommand(): void
    {
        $serializer = new CommandSerializer();
        $command = new SimpleCommand(1, 'test');

        self::assertEquals(
            $command,
            $serializer->deserialize(
                $serializer->serialize($command)
            )
        );
    }
}
