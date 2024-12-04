<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class OrderWaitingForProcessPaymentUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'order-process-payment';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
