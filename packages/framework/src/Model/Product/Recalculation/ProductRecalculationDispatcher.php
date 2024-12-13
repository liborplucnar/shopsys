<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\InvalidScopeException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    public const int DISPATCH_BATCH_SIZE = 5000;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig $productExportScopeConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationCacheFacade $productRecalculationCacheFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationRepository $productRecalculationRepository
     */
    public function __construct(
        protected readonly ProductExportScopeConfig $productExportScopeConfig,
        protected readonly ProductRecalculationCacheFacade $productRecalculationCacheFacade,
        protected readonly ProductRecalculationRepository $productRecalculationRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProducts(
        array $products,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        return $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $productRecalculationPriorityEnum,
            $exportScopes,
        );
    }

    /**
     * @param int[] $productIds
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        $this->verifyExportScopes($exportScopes);
        $productIds = array_unique($productIds);
        $dispatchedIds = [];
        $productIdsCount = count($productIds);

        for ($i = 0; $i < $productIdsCount; $i += self::DISPATCH_BATCH_SIZE) {
            $productIdsBatch = array_slice($productIds, $i, self::DISPATCH_BATCH_SIZE);
            $idsToRecalculate = $this->productRecalculationRepository->getIdsToRecalculate($productIdsBatch);
            $filteredProductIds = $this->filterNotCachedProductIds($idsToRecalculate, $exportScopes, $productRecalculationPriorityEnum);

            foreach ($filteredProductIds as $productId) {
                $message = match ($productRecalculationPriorityEnum) {
                    ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId, $exportScopes),
                    ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId, $exportScopes),
                    default => throw new UnknownProductRecalculationPriorityException($productRecalculationPriorityEnum),
                };
                $this->messageBus->dispatch($message);

                $dispatchedIds[] = $productId;
            }
        }

        return $dispatchedIds;
    }

    /**
     * @param int $productId
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     */
    public function dispatchSingleProductId(
        int $productId,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriorityEnum, $exportScopes);
    }

    /**
     * @param string[] $exportScopes
     */
    public function dispatchAllProducts(array $exportScopes = []): void
    {
        $this->verifyExportScopes($exportScopes);
        $this->messageBus->dispatch(new DispatchAllProductsMessage($exportScopes));
    }

    /**
     * @param string[] $exportScopes
     */
    protected function verifyExportScopes(array $exportScopes): void
    {
        foreach ($exportScopes as $scope) {
            if (!in_array($scope, $this->productExportScopeConfig->getAllProductExportScopes(), true)) {
                throw new InvalidScopeException($scope);
            }
        }
    }

    /**
     * @param int[] $productIds
     * @param string[] $scopes
     * @param string $productRecalculationPriorityEnum
     * @return int[]
     */
    protected function filterNotCachedProductIds(
        array $productIds,
        array $scopes,
        string $productRecalculationPriorityEnum,
    ): array {
        $filteredProductIds = [];
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            if ($productRecalculationPriorityEnum === ProductRecalculationPriorityEnum::HIGH) {
                $filteredProductIds[] = $productId;

                continue;
            }

            $exists = $this->productRecalculationCacheFacade->contains($productId);
            $this->productRecalculationCacheFacade->save($productId, $scopes);

            if ($exists) {
                continue;
            }

            $filteredProductIds[] = $productId;
        }

        return $filteredProductIds;
    }

    /**
     * @param int[] $productIds
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     */
    protected function dispatchProductIdsBatch(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->verifyExportScopes($exportScopes);

        $this->messageBus->dispatch(
            new DispatchProductIdsBatchMessage(
                $productIds,
                $exportScopes,
                $productRecalculationPriorityEnum,
            ),
        );
    }

    /**
     * @param array $productIds
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     */
    public function dispatchProductIdsByBatches(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $productIdsCount = count($productIds);

        for ($i = 0; $i < $productIdsCount; $i += self::DISPATCH_BATCH_SIZE) {
            $productIdsBatch = array_slice($productIds, $i, self::DISPATCH_BATCH_SIZE);
            $this->dispatchProductIdsBatch($productIdsBatch, $productRecalculationPriorityEnum, $exportScopes);
        }
    }
}
