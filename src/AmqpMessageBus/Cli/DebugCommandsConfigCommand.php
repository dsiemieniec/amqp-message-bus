<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Cli;

use Siemieniec\AmqpMessageBus\Config\Config;
use Siemieniec\AmqpMessageBus\Exception\HandlerMissingException;
use Siemieniec\AmqpMessageBus\Handler\HandlerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'async-commands:debug'
)]
class DebugCommandsConfigCommand extends Command
{
    /**
     * @param string[] $commands
     */
    public function __construct(
        private Config $config,
        private HandlerRegistry $handlerRegistry,
        private array $commands
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['Command', 'Handler', 'Serializer', 'Publisher config']);
        foreach ($this->commands as $command) {
            $table->addRow(new TableSeparator());
            $publisherConfig = $this->config->getCommandConfig($command)->getPublisherConfig();

            $publisher = [
                \sprintf('Connection: %s', $publisherConfig->getConnection()->getName())
            ];

            if ($publisherConfig->getPublisherTarget()->getExchange() === '') {
                $publisher[] = \sprintf('Queue: %s', $publisherConfig->getPublisherTarget()->getRoutingKey());
            } else {
                $publisher[] = \sprintf('Exchange: %s', $publisherConfig->getPublisherTarget()->getExchange());
                $publisher[] = \sprintf(
                    'Routing key: %s',
                    $publisherConfig->getPublisherTarget()->getRoutingKey()
                );
            }

            $table->addRow([
                $command,
                $this->getHandlerClass($command),
                $this->config->getCommandConfig($command)->getSerializerClass(),
                \implode(PHP_EOL, $publisher)
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }

    private function getHandlerClass(string $commandClass): string
    {
        try {
            return \get_class($this->handlerRegistry->getHandlerByClass($commandClass));
        } catch (HandlerMissingException) {
            return 'Missing handler';
        }
    }
}
