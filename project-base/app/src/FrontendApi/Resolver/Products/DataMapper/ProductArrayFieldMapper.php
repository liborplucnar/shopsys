<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\DataMapper;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper as BaseProductArrayFieldMapper;

/**
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @property \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method \App\Model\Category\Category[] getCategories(array $data)
 * @method \App\Model\Product\Flag\Flag[] getFlags(array $data)
 * @method \App\Model\Product\Brand\Brand|null getBrand(array $data)
 * @property \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class ProductArrayFieldMapper extends BaseProductArrayFieldMapper
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableCountByIdsBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $categoriesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $flagsBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $brandsBatchLoader
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ProductElasticsearchProvider $productElasticsearchProvider,
        ParameterWithValuesFactory $parameterWithValuesFactory,
        ProductFrontendLimitProvider $productFrontendLimitProvider,
        DataLoaderInterface $productsSellableByIdsBatchLoader,
        CurrentCustomerUser $currentCustomerUser,
        DataLoaderInterface $productsSellableCountByIdsBatchLoader,
        private DataLoaderInterface $categoriesBatchLoader,
        private DataLoaderInterface $flagsBatchLoader,
        private DataLoaderInterface $brandsBatchLoader,
    ) {
        parent::__construct(
            $categoryFacade,
            $flagFacade,
            $brandFacade,
            $productElasticsearchProvider,
            $parameterWithValuesFactory,
            $productFrontendLimitProvider,
            $productsSellableByIdsBatchLoader,
            $currentCustomerUser,
            $productsSellableCountByIdsBatchLoader,
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isSellingDenied(array $data): bool
    {
        return $data['calculated_selling_denied'] === true || $data['is_sale_exclusion'] === true;
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getPartNumber(array $data): ?string
    {
        return $data['partno'];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getCatalogNumber(array $data): string
    {
        return $data['catnum'];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getSlug(array $data): string
    {
        return '/' . $data['slug'];
    }

    /**
     * @param array $data
     * @return int[]
     */
    public function getRelatedProducts(array $data): array
    {
        return $this->productElasticsearchProvider->getSellableProductArrayByIds(
            $data['related_products'],
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );
    }

    /**
     * @param array $data
     * @return array
     */
    public function getBreadcrumb(array $data): array
    {
        return $data['breadcrumb'];
    }

    /**
     * @param array $data
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getCategoriesPromise(array $data): Promise
    {
        return $this->categoriesBatchLoader->load($data['categories']);
    }

    /**
     * @param array $data
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getFlagsPromise(array $data): Promise
    {
        return $this->flagsBatchLoader->load($data['flags']);
    }

    /**
     * @param array $data
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function getRelatedProductsPromise(array $data): Promise
    {
        return $this->productsSellableByIdsBatchLoader->load($data['related_products']);
    }

    /**
     * @param array $data
     * @return \GraphQL\Executor\Promise\Promise|null
     */
    public function getBrandPromise(array $data): ?Promise
    {
        $brandId = $data['brand'];

        return $brandId !== '' ? $this->brandsBatchLoader->load($brandId) : null;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isMainVariant(array $data): bool
    {
        return $data['is_main_variant'];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getProductVideos(array $data): array
    {
        return $data['product_videos'];
    }
}
