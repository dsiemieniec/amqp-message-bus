<?php

declare(strict_types=1);

namespace App\Cli;

use App\Exception\MessageLimitException;
use App\Exception\TimeLimitException;
use App\Rabbit\CommandConsumer;
use App\Rabbit\CommandConsumerFactory;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:consume-commands'
)]
class ConsumeCommandsCommand extends Command
{
    private CommandConsumer $consumer;
    private string $name;

    public function __construct(
        private CommandConsumerFactory $consumerFactory
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
        $io = new SymfonyStyle($input, $output);
        try {
            $this->name = $input->getArgument('name');
            $output->writeln(\sprintf('Starting %s consumer...', $this->name));

            $this->consumer = $this->consumerFactory->create($this->name);
            $this->consumer->consume();
        } catch (MessageLimitException | TimeLimitException | AMQPTimeoutException $exception) {
            $io->warning($exception->getMessage());
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function onShutdown(int $signalNumber): void
    {
        $signals = [SIGINT => 'SIGINT', SIGQUIT => 'SIGQUIT', SIGTERM => 'SIGTERM'];
        echo \sprintf(
            '%s received. %s consumer will stop after handling current command',
            $signals[$signalNumber],
            $this->name
        ) . PHP_EOL;
        $this->consumer->stop();
    }
}
