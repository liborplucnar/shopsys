<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

class ArticleBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    protected ArticleRepository $articleRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     */
    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $article = $this->articleRepository->getById($routeParameters['id']);

        return [
            new BreadcrumbItem($article->getName()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteNames()
    {
        return ['front_article_detail'];
    }
}
