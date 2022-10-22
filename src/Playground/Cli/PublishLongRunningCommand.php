<?php

declare(strict_types=1);

namespace App\Cli;

use Siemieniec\AsyncCommandBus\Command\CommandBusInterface;
use App\Command\LongRunningCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:publish-long-running'
)]
class PublishLongRunningCommand extends Command
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('seconds', InputArgument::REQUIRED)
            ->addArgument('numberOfCommands', InputArgument::REQUIRED, 'Number of commands to publish')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $seconds = (int)$input->getArgument('seconds');
        $numberOfCommands = (int)$input->getArgument('numberOfCommands');
        $progressBar = new ProgressBar($output, $numberOfCommands);

        $progressBar->start();

        for ($i = 0; $i < $numberOfCommands; $i++) {
            $this->commandBus->executeAsync(new LongRunningCommand($seconds));

            $progressBar->advance();
        }
        $progressBar->finish();
        $io->success('Done');


        return Command::SUCCESS;
    }
}