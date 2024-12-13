<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class FreeTransportAndPaymentInformationMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     */
    public function __construct(protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderProcessingData->orderData->freeTransportAndPaymentApplied = $this->freeTransportAndPaymentFacade->isFreeTransportAndPaymentApplied(
            $orderProcessingData->getDomainConfig()->getId(),
            $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
            $orderProcessingData->orderInput->isFreeTransportAndPaymentPromoCodeApplied(),
        );

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
