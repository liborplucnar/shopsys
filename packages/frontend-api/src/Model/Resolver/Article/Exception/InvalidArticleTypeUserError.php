<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class InvalidArticleTypeUserError extends UserError implements UserErrorWithCodeInterface
{
    public const CODE = 'invalid-article-type';

    /**
     * @return string
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
