<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;

class ProductEntityFieldMapper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableCountByIdsBatchLoader
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly ProductAccessoryFacade $productAccessoryFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly DataLoaderInterface $productsSellableCountByIdsBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getShortDescription(Product $product): ?string
    {
        return $product->getShortDescription($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getLink(Product $product): string
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            [$product->getId()],
            $this->domain->getCurrentDomainConfig(),
        );

        return $absoluteUrlsIndexedByProductId[$product->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(Product $product): array
    {
        return $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array{name: string, status: string}
     */
    public function getAvailability(Product $product): array
    {
        return [
            'name' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
                $product,
                $this->domain->getId(),
            ),
            'status' => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId(
                $product,
                $this->domain->getId(),
            ),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isSellingDenied(Product $product): bool
    {
        return $product->getCalculatedSellingDenied();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getAccessoriesPromise(Product $product): Promise
    {
        $accessories = $this->productAccessoryFacade->getOfferedAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        $accessoriesIds = array_map(fn (Product $accessory) => $accessory->getId(), $accessories);

        return $this->productsSellableByIdsBatchLoader->load($accessoriesIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getDescription(Product $product): ?string
    {
        return $product->getDescription($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues[]
     */
    public function getParameters(Product $product): array
    {
        return $this->parameterWithValuesFactory->createMultipleForProduct($product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoH1(Product $product): ?string
    {
        return $product->getSeoH1($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoTitle(Product $product): ?string
    {
        return $product->getSeoTitle($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoMetaDescription(Product $product): ?string
    {
        return $product->getSeoMetaDescription($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int
     */
    public function getOrderingPriority(Product $product): int
    {
        return $product->getOrderingPriority($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getHreflangLinks(Product $product): array
    {
        return $this->hreflangLinksFacade->getForProduct($product, $this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isVisible(Product $product): bool
    {
        $productVisibility = $this->productVisibilityFacade->getProductVisibility(
            $product,
            $this->currentCustomerUser->getPricingGroup(),
            $this->domain->getId(),
        );

        return $productVisibility->isVisible();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getVariants(Product $product): Promise
    {
        $variantIds = array_map(static fn (Product $variant) => $variant->getId(), $product->getVariants());

        return $this->productsSellableByIdsBatchLoader->load($variantIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getVariantsCount(Product $product): Promise
    {
        $variantIds = array_map(static fn (Product $variant) => $variant->getId(), $product->getVariants());

        return $this->productsSellableCountByIdsBatchLoader->load($variantIds);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isInquiryType(Product $product): bool
    {
        return $product->getProductType() === ProductTypeEnum::TYPE_INQUIRY;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getProductType(Product $product): string
    {
        return $product->getProductType();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getNameSuffix(Product $product): ?string
    {
        return $product->getNameSuffix($this->domain->getLocale());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getNamePrefix(Product $product): ?string
    {
        return $product->getNamePrefix($this->domain->getLocale());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getFullName(Product $product): string
    {
        return $product->getFullName($this->domain->getLocale());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int|null
     */
    public function getStockQuantity(Product $product): ?int
    {
        return $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    public function getStoreAvailabilities(Product $product): array
    {
        $storeAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(
            $product,
            $this->domain->getId(),
        );

        $result = [];

        foreach ($storeAvailabilitiesInformation as $storeAvailabilityInformation) {
            $result[] = [
                'store_name' => $storeAvailabilityInformation->getStoreName(),
                'store_id' => $storeAvailabilityInformation->getStoreId(),
                'availability_information' => $storeAvailabilityInformation->getAvailabilityInformation(),
                'availability_status' => $storeAvailabilityInformation->getAvailabilityStatus(),
            ];
        }

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int|null
     */
    public function getAvailableStoresCount(Product $product): ?int
    {
        return $this->productAvailabilityFacade->getAvailableStoresCount(
            $product,
            $this->domain->getId(),
        );
    }
}
