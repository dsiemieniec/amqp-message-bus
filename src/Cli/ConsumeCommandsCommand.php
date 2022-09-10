<?php

namespace App\Cli;

use App\Rabbit\CommandConsumerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:consume-commands'
)]
class ConsumeCommandsCommand extends Command
{
    public function __construct(
        private CommandConsumerInterface $consumer
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('name', InputOption::VALUE_REQUIRED, '', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln(" [*] Waiting for messages. To exit press CTRL+C\n");
        $this->consumer->consume($input->getArgument('name'));

        return Command::SUCCESS;
    }
}
