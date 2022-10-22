<?php

declare(strict_types=1);

namespace Siemieniec\AsyncCommandBus\Cli;

use Siemieniec\AsyncCommandBus\Exception\MessageLimitException;
use Siemieniec\AsyncCommandBus\Exception\TimeLimitException;
use Siemieniec\AsyncCommandBus\Rabbit\CommandConsumer;
use Siemieniec\AsyncCommandBus\Rabbit\CommandConsumerFactory;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'async-commands:consume'
)]
class ConsumeCommandsCommand extends Command
{
    private CommandConsumer $consumer;
    private string $name;
    private SymfonyStyle $io;

    public function __construct(
        private CommandConsumerFactory $consumerFactory,
        private LoggerInterface $logger
    ) {
        parent::__construct();

        $callable = fn(int $signalNumber) => $this->onShutdown($signalNumber);
        \pcntl_signal(SIGINT, $callable);
        \pcntl_signal(SIGQUIT, $callable);
        \pcntl_signal(SIGTERM, $callable);
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputOption::VALUE_REQUIRED, 'Queue name', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        try {
            $this->name = $input->getArgument('name');
            $this->io->info(\sprintf('Starting %s consumer...', $this->name));

            $this->consumer = $this->consumerFactory->create($this->name);
            $this->consumer->consume();
        } catch (MessageLimitException | TimeLimitException | AMQPTimeoutException $exception) {
            $this->io->warning($exception->getMessage());
            $this->logger->warning($exception->getMessage());
        } catch (Throwable $exception) {
            $this->io->error($exception->getMessage());
            $this->logger->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function onShutdown(int $signalNumber): void
    {
        $signals = [SIGINT => 'SIGINT', SIGQUIT => 'SIGQUIT', SIGTERM => 'SIGTERM'];
        $message = \sprintf(
            '%s received. %s consumer will stop after handling current command',
            $signals[$signalNumber],
            $this->name
        );
        $this->io->warning($message);
        $this->logger->warning($message);
        $this->consumer->stop();
    }
}
