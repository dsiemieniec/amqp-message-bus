<?php

declare(strict_types=1);

namespace Siemieniec\AmqpMessageBus\Cli;

use Siemieniec\AmqpMessageBus\Exception\BindingDeclarationException;
use Siemieniec\AmqpMessageBus\Exception\ExchangeDeclarationException;
use Siemieniec\AmqpMessageBus\Exception\QueueDeclarationException;
use Siemieniec\AmqpMessageBus\Rabbit\RabbitManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'amqp-message-bus:setup-rabbit',
    description: 'Declares queues, exchanges and bindings',
)]
class SetupRabbitCommand extends Command
{
    public function __construct(
        private RabbitManager $rabbitManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->rabbitManager->declareAll();
            $io->success('All declared');

            return Command::SUCCESS;
        } catch (QueueDeclarationException $exception) {
            $io->error(
                \sprintf(
                    'Queue: %s. %s. %s',
                    $exception->getQueue()->getName(),
                    $exception->getMessage(),
                    $exception->getPrevious()?->getMessage() ?? ''
                )
            );
        } catch (ExchangeDeclarationException $exception) {
            $io->error(
                \sprintf(
                    'Exchange: %s. %s. %s',
                    $exception->getExchange()->getName(),
                    $exception->getMessage(),
                    $exception->getPrevious()?->getMessage() ?? ''
                )
            );
        } catch (BindingDeclarationException $exception) {
            $io->error(
                \sprintf(
                    'Failed to bind queue %s to exchange %s with routing key %s. %s',
                    $exception->getQueueBinding()->getQueue()->getName(),
                    $exception->getExchange()->getName(),
                    $exception->getQueueBinding()->getRoutingKey(),
                    $exception->getPrevious()?->getMessage() ?? ''
                )
            );
        } catch (Throwable $exception) {
            $io->error($exception->getMessage());
        }

        return Command::FAILURE;
    }
}
