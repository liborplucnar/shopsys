<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class BlogCategoryResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'BlogCategory' => [
                'seoH1' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoH1($this->domain->getId());
                },
                'seoTitle' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoTitle($this->domain->getId());
                },
                'seoMetaDescription' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoMetaDescription($this->domain->getId());
                },
                'parent' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getParent();
                },
                'slug' => function (BlogCategory $blogCategory) {
                    return '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug($this->domain->getId(), 'front_blogcategory_detail', $blogCategory->getId());
                },
                'link' => function (BlogCategory $blogCategory) {
                    return $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain('front_blogcategory_detail', $blogCategory->getId());
                },
                'children' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
                        $blogCategory,
                        $this->domain->getId(),
                    );
                },
                'blogCategoriesTree' => function () {
                    return $this->blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(
                        $this->domain->getId(),
                    );
                },
                'articlesTotalCount' => function (BlogCategory $blogCategory) {
                    return $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory);
                },
                'hreflangLinks' => function (BlogCategory $blogCategory) {
                    return $this->hreflangLinksFacade->getForBlogCategory($blogCategory, $this->domain->getId());
                },
            ],
        ];
    }
}
