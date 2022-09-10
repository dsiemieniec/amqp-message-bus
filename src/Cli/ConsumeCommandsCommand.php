<?php

namespace App\Cli;

use App\Exception\MessageLimitException;
use App\Exception\TimeLimitException;
use App\Rabbit\CommandConsumerInterface;
use App\Rabbit\ConsumerLimits;
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
    public function __construct(
        private CommandConsumerInterface $consumer
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('name', InputOption::VALUE_REQUIRED, '', 'default');
        $this->addOption('time-limit', null, InputOption::VALUE_OPTIONAL, 'Consumer time limit in seconds', 0);
        $this->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Consumer timeout limit in seconds', 0);
        $this->addOption('message-limit', null, InputOption::VALUE_OPTIONAL, 'Consumed messages limit', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $limits = new ConsumerLimits(
                (int)$input->getOption('time-limit'),
                (int)$input->getOption('timeout'),
                (int)$input->getOption('message-limit')
            );

            $name = $input->getArgument('name');
            $output->writeln(\sprintf('Consuming %s...', $name));

            $this->consumer->consume($name, $limits);
        } catch (MessageLimitException|TimeLimitException|AMQPTimeoutException $exception) {
            $io->warning($exception->getMessage());
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
