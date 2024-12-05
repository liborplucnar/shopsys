<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class ProductVisibilityFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        protected readonly ProductVisibilityRepository $productVisibilityRepository,
        protected readonly Domain $domain,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function calculateProductVisibilityForIds(array $productIds): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility($productIds);
    }

    public function calculateProductVisibilityForAll(): void
    {
        $this->productVisibilityRepository->refreshProductsVisibility();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[]
     */
    public function findProductVisibilitiesByDomainIdAndProduct(int $domainId, Product $product): array
    {
        return $this->productVisibilityRepository->findProductVisibilitiesByDomainIdAndProduct($domainId, $product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
     * @return bool
     */
    public function isProductVisibleOnAllDomains(
        Product $product,
        array $defaultPricingGroupIdsIndexedByDomainId,
    ): bool {
        $count = $this->productVisibilityRepository->getCountOfDomainsProductIsVisibleOn(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );

        return $count === count($defaultPricingGroupIdsIndexedByDomainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
     * @return bool
     */
    public function isProductVisibleOnSomeDomains(
        Product $product,
        array $defaultPricingGroupIdsIndexedByDomainId,
    ): bool {
        $count = $this->productVisibilityRepository->getCountOfDomainsProductIsVisibleOn(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );

        return $count > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility
     */
    public function getProductVisibility(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): ProductVisibility {
        return $this->productVisibilityRepository->getProductVisibility($product, $pricingGroup, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     */
    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, int $domainId): void
    {
        $this->productVisibilityRepository->createAndRefreshProductVisibilitiesForPricingGroup($pricingGroup, $domainId);
    }

    /**
     * @param int[] $productIds
     * @return bool[]
     */
    public function areProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId(array $productIds): array
    {
        $productVisibilitiesIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVisibilitiesIndexedByDomainId[$domainId] = $this->getProductsVisibilitiesByDomainIndexedByProductId(
                $productIds,
                $domainId,
            );
        }

        $productVisibilitiesIndexedByProductId = array_fill_keys($productIds, false);

        foreach ($productVisibilitiesIndexedByDomainId as $productVisibilities) {
            foreach ($productVisibilities as $productId => $productVisibility) {
                if ($productVisibilitiesIndexedByProductId[$productId] === false) {
                    $productVisibilitiesIndexedByProductId[$productId] = $productVisibility;
                }
            }
        }

        return $productVisibilitiesIndexedByProductId;
    }

    /**
     * @param int[] $productIds
     * @return bool[]
     */
    public function areProductsVisibleForDefaultPricingGroupOnEachDomainIndexedByProductId(array $productIds): array
    {
        $productVisibilitiesIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVisibilitiesIndexedByDomainId[$domainId] = $this->getProductsVisibilitiesByDomainIndexedByProductId(
                $productIds,
                $domainId,
            );
        }

        $productVisibilitiesIndexedByProductId = array_fill_keys($productIds, true);

        foreach ($productVisibilitiesIndexedByDomainId as $productVisibilities) {
            foreach ($productVisibilities as $productId => $productVisibility) {
                if ($productVisibilitiesIndexedByProductId[$productId]) {
                    $productVisibilitiesIndexedByProductId[$productId] = $productVisibility;
                }
            }
        }

        return $productVisibilitiesIndexedByProductId;
    }

    /**
     * @param int[] $productIds
     * @param int $domainId
     * @return bool[]
     */
    protected function getProductsVisibilitiesByDomainIndexedByProductId(array $productIds, int $domainId): array
    {
        $pricingGroupId = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId)->getId();

        return $this->productVisibilityRepository->getProductsVisibilitiesByPricingGroupAndDomainIndexedByProductId(
            $productIds,
            $pricingGroupId,
            $domainId,
        );
    }
}
