<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGeneratorFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:generate:friendly-url',
    description: 'Generate friendly urls for supported entities',
)]
class GenerateFriendlyUrlCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGeneratorFacade $friendlyUrlGeneratorFacade
     */
    public function __construct(private readonly FriendlyUrlGeneratorFacade $friendlyUrlGeneratorFacade)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            '<fg=green>Start of generating missing friendly urls from routing_friendly_url.yaml file.</fg=green>',
        );

        $this->friendlyUrlGeneratorFacade->generateUrlsForSupportedEntities($output);

        $output->writeln('<fg=green>Generating complete.</fg=green>');

        return Command::SUCCESS;
    }
}
