<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Monolog\Logger;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class ErrorPageCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     */
    public function __construct(protected readonly ErrorPagesFacade $errorPagesFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->errorPagesFacade->generateAllErrorPagesForProduction();
    }
}
