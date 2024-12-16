<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\InvalidArticleTypeUserError;

class NotBlogArticleResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'NotBlogArticleInterface' => [
                self::RESOLVE_TYPE => $this->getResolveType(...),
            ],
        ];
    }

    /**
     * @param array $data
     * @return string
     */
    private function getResolveType(array $data): string
    {
        return match ($data['type']) {
            Article::TYPE_SITE => 'ArticleSite',
            Article::TYPE_LINK => 'ArticleLink',
            default => throw new InvalidArticleTypeUserError(),
        };
    }
}
