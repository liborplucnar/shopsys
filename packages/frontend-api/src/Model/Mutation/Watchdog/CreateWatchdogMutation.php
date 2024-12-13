<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Watchdog;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogDataFactory;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class CreateWatchdogMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogDataFactory $watchdogDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade $watchdogFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly WatchdogDataFactory $watchdogDataFactory,
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly Domain $domain,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function createWatchdogMutation(Argument $argument): bool
    {
        try {
            $watchdogData = $this->createWatchdogDataFromArgument($argument);

            $watchdog = $this->watchdogFacade->findByProductUuidEmailAndDomainId(
                $watchdogData->product,
                $watchdogData->email,
                $watchdogData->domainId,
            );

            if ($watchdog !== null) {
                $this->watchdogFacade->updateValidity($watchdog->getId());
            } else {
                $this->watchdogFacade->create($watchdogData);
            }

            return true;
        } catch (ProductNotFoundException) {
            throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $argument['input']['productUuid']));
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData
     */
    protected function createWatchdogDataFromArgument(Argument $argument): WatchdogData
    {
        $input = $argument['input'];

        $watchdogData = $this->watchdogDataFactory->createByDomainId($this->domain->getId());
        $product = $this->productFacade->getByUuid($input['productUuid']);

        $watchdogData->product = $product;
        $watchdogData->email = $input['email'];

        return $watchdogData;
    }
}
