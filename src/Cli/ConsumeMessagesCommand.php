<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Cli;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use Psr\Log\LoggerInterface;
use Siemieniec\AmqpMessageBus\Exception\MessageLimitException;
use Siemieniec\AmqpMessageBus\Exception\TimeLimitException;
use Siemieniec\AmqpMessageBus\Rabbit\MessageConsumer;
use Siemieniec\AmqpMessageBus\Rabbit\MessageConsumerFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'amqp-message-bus:consume'
)]
class ConsumeMessagesCommand extends Command
{
    private ?MessageConsumer $consumer = null;
    private string $name = '';

    public function __construct(
        private MessageConsumerFactory $consumerFactory,
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
        $io = new SymfonyStyle($input, $output);
        $return = Command::SUCCESS;
        try {
            $this->name = $input->getArgument('name');
            $io->info(\sprintf('Starting %s consumer...', $this->name));

            $this->consumer = $this->consumerFactory->create($this->name);
            $this->consumer->consume();
        } catch (MessageLimitException | TimeLimitException | AMQPTimeoutException $exception) {
            $this->logger->warning($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            $return = Command::FAILURE;
        } finally {
            $this->consumer?->stop();
        }

        return $return;
    }

    private function onShutdown(int $signalNumber): void
    {
        $signals = [SIGINT => 'SIGINT', SIGQUIT => 'SIGQUIT', SIGTERM => 'SIGTERM'];
        $message = \sprintf(
            '%s received. %s consumer will stop after handling current command',
            $signals[$signalNumber],
            $this->name
        );
        $this->logger->warning($message);
        $this->consumer?->stop();
    }
}
