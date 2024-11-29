<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfo;

class ProductPriceResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'ProductPrice' => [
                'isPriceFrom' => function (ProductPrice|PriceInfo $productPrice) {
                    if ($productPrice instanceof PriceInfo) {
                        return $productPrice->priceFrom;
                    }

                    return $productPrice->isPriceFrom();
                },
            ],
        ];
    }
}
