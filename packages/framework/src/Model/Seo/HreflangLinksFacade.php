<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;

class HreflangLinksFacade
{
    protected const string ROUTE_PRODUCT_DETAIL = 'front_product_detail';
    protected const string ROUTE_PRODUCT_LIST = 'front_product_list';
    protected const string ROUTE_BRAND_DETAIL = 'front_brand_detail';
    protected const string ROUTE_FLAG_DETAIL = 'front_flag_detail';
    protected const string ROUTE_BLOG_ARTICLE_DETAIL = 'front_blogarticle_detail';
    protected const string ROUTE_BLOG_CATEGORY_DETAIL = 'front_blogcategory_detail';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink
     */
    public function createHreflangLink(int $domainId, string $routeName, int $entityId): HreflangLink
    {
        return new HreflangLink(
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $domainId,
                $routeName,
                $entityId,
            ),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForProduct(Product $product, int $currentDomainId): array
    {
        $isVisibleCallable = function (Product $product, int $domainId): bool {
            $productVisibility = $this->productVisibilityFacade->getProductVisibility(
                $product,
                $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
                $domainId,
            );

            return $productVisibility->isVisible();
        };

        return $this->doGetHrefLinks(
            $product,
            $currentDomainId,
            static::ROUTE_PRODUCT_DETAIL,
            $isVisibleCallable,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForCategory(Category $category, int $currentDomainId): array
    {
        $isVisibleCallable = static function (Category $category, int $domainId): bool {
            return $category->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $category,
            $currentDomainId,
            static::ROUTE_PRODUCT_LIST,
            $isVisibleCallable,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBrand(Brand $brand, int $currentDomainId): array
    {
        return $this->doGetHrefLinks($brand, $currentDomainId, static::ROUTE_BRAND_DETAIL);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBlogArticle(BlogArticle $blogArticle, int $currentDomainId): array
    {
        $isVisibleCallable = static function (BlogArticle $blogArticle, int $domainId): bool {
            return $blogArticle->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $blogArticle,
            $currentDomainId,
            static::ROUTE_BLOG_ARTICLE_DETAIL,
            $isVisibleCallable,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForBlogCategory(BlogCategory $blogCategory, int $currentDomainId): array
    {
        $isVisibleCallable = static function (BlogCategory $blogCategory, int $domainId): bool {
            return $blogCategory->isVisible($domainId);
        };

        return $this->doGetHrefLinks(
            $blogCategory,
            $currentDomainId,
            static::ROUTE_BLOG_CATEGORY_DETAIL,
            $isVisibleCallable,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForFlag(Flag $flag, int $currentDomainId): array
    {
        return $this->doGetHrefLinks($flag, $currentDomainId, static::ROUTE_FLAG_DETAIL);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getForSeoPage(SeoPage $seoPage, int $currentDomainId): array
    {
        return $this->doGetHrefLinks($seoPage, $currentDomainId, '');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle|\Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\Shopsys\FrameworkBundle\Model\Category\Category|\Shopsys\FrameworkBundle\Model\Product\Product|\Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|\Shopsys\FrameworkBundle\Model\Product\Flag\Flag $entity
     * @param int $currentDomainId
     * @param string $routeName
     * @param callable|null $isVisibleCallable
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    protected function doGetHrefLinks(
        BlogArticle|BlogCategory|Brand|Category|Product|SeoPage|Flag $entity,
        int $currentDomainId,
        string $routeName,
        ?callable $isVisibleCallable = null,
    ): array {
        $domainIds = $this->getRelevantDomainIds($currentDomainId);

        $result = [];

        foreach ($domainIds as $domainId) {
            if ($isVisibleCallable !== null && $isVisibleCallable($entity, $domainId) === false) {
                continue;
            }

            if ($entity instanceof SeoPage) {
                $result[] = $this->createHreflangLinkForSeoPage($domainId, $entity);
            } else {
                $result[] = $this->createHreflangLink($domainId, $routeName, $entity->getId());
            }
        }

        return $result;
    }

    /**
     * @param int $currentDomainId
     * @return int[]
     */
    protected function getRelevantDomainIds(int $currentDomainId): array
    {
        $alternativeDomainIds = $this->seoSettingFacade->getAlternativeDomainsForDomain($currentDomainId);

        return [$currentDomainId, ...$alternativeDomainIds];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink
     */
    protected function createHreflangLinkForSeoPage(int $domainId, SeoPage $seoPage): HreflangLink
    {
        return new HreflangLink(
            $this->domain->getDomainConfigById($domainId)->getLocale(),
            $this->domain->getUrl() . '/' . $seoPage->getPageSlug($domainId),
        );
    }
}
