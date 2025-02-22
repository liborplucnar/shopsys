<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Monolog\Logger;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class TransferIssueLogCleanerCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     */
    public function __construct(
        protected readonly TransferIssueRepository $transferIssueRepository,
    ) {
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->logger->info('Start clear transfer issue table');
        $this->transferIssueRepository->deleteOldTransferIssues();
        $this->logger->info('End of clear transfer issue table');
    }
}
