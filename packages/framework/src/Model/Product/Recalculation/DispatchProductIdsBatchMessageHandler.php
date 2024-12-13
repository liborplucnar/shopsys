<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DispatchProductIdsBatchMessageHandler implements MessageHandlerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchProductIdsBatchMessage $message
     */
    public function __invoke(DispatchProductIdsBatchMessage $message): void
    {
        foreach ($message->productIds as $productId) {
            $this->productRecalculationDispatcher->dispatchSingleProductId(
                $productId,
                $message->productRecalculationPriorityEnum,
                $message->exportScopes,
            );
        }
    }
}
