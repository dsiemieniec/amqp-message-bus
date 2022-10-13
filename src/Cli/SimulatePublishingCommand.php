<?php

declare(strict_types=1);

namespace App\Cli;

use App\Command\AnotherSimpleCommand;
use App\Command\CommandBusInterface;
use App\Command\CommandInterface;
use App\Command\DispatchedToOwnQueueCommand;
use App\Command\Properties\CommandProperties;
use App\Command\SimpleCommand;
use App\Utils\Delay;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:simulate-publishing',
)]
class SimulatePublishingCommand extends Command
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('numberOfCommands', InputArgument::REQUIRED, 'Number of commands to publish')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $numberOfCommands = (int)$input->getArgument('numberOfCommands');
        $progressBar = new ProgressBar($output, $numberOfCommands);

        $progressBar->start();

        for ($i = 0; $i < $numberOfCommands; $i++) {
            $properties = CommandProperties::builder()
                ->addHeader('x-delay', (string) Delay::seconds(\random_int(1, 15)))
                ->build();
            $this->commandBus->executeAsync($this->getRandomCommand(), $properties);

            $progressBar->advance();
        }
        $progressBar->finish();
        $io->success('Done');

        return Command::SUCCESS;
    }

    public function getRandomCommand(): CommandInterface
    {
        $i = \random_int(0, 2);
        if ($i === 0) {
            return new SimpleCommand(\random_int($i, 99999999), \uniqid('', true));
        } elseif ($i === 1) {
            return new AnotherSimpleCommand(
                \uniqid('', true),
                \uniqid('', true),
                new DateTimeImmutable()
            );
        }

        return new DispatchedToOwnQueueCommand(\random_int($i, 99999999), \uniqid('', true));
    }
}
