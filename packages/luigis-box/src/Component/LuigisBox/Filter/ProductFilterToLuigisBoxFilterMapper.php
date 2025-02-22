<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterToLuigisBoxFilterMapper
{
    protected const string FILTER_OR = 'f';
    protected const string FILTER_AND = 'f_must';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    public function createEmpty(): array
    {
        return [
            self::FILTER_AND => [],
            self::FILTER_OR => [],
        ];
    }

    /**
     * @param string $luigisBoxType
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @return array
     */
    public function map(string $luigisBoxType, ProductFilterData $productFilterData, Domain $domain): array
    {
        $luigisBoxFilter = $this->createEmpty();
        $luigisBoxFilter = $this->mapType($luigisBoxType, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapPrice($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapAvailability($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapFlags($productFilterData, $luigisBoxFilter, $domain->getLocale());
        $luigisBoxFilter = $this->mapBrands($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapParameters($productFilterData, $luigisBoxFilter);

        return $luigisBoxFilter;
    }

    /**
     * @param string $luigisBoxType
     * @return array
     */
    public function mapOnlyType(string $luigisBoxType): array
    {
        $luigisBoxFilter = $this->createEmpty();
        $luigisBoxFilter = $this->mapType($luigisBoxType, $luigisBoxFilter);

        return $luigisBoxFilter;
    }

    /**
     * @param string $luigisBoxType
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapType(string $luigisBoxType, array $luigisBoxFilter): array
    {
        $luigisBoxFilter[self::FILTER_OR][] = 'type:' . $luigisBoxType;

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapPrice(ProductFilterData $productFilterData, array $luigisBoxFilter): array
    {
        if ($productFilterData->minimalPrice !== null || $productFilterData->maximalPrice !== null) {
            $priceFrom = $productFilterData->minimalPrice === null ? '' : $productFilterData->minimalPrice->getAmount();
            $priceTo = $productFilterData->maximalPrice === null ? '' : $productFilterData->maximalPrice->getAmount();

            $luigisBoxFilter[self::FILTER_OR][] = 'price_amount:' . $priceFrom . '|' . $priceTo;
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapAvailability(
        ProductFilterData $productFilterData,
        array $luigisBoxFilter,
    ): array {
        if ($productFilterData->inStock === true) {
            $luigisBoxFilter[self::FILTER_OR][] = 'availability_rank_text:' . $this->productAvailabilityFacade->getOnStockText($this->domain->getLocale());
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @param string $locale
     * @return array
     */
    protected function mapFlags(ProductFilterData $productFilterData, array $luigisBoxFilter, string $locale): array
    {
        foreach ($productFilterData->flags as $flag) {
            $luigisBoxFilter[self::FILTER_OR][] = 'labels:' . $flag->getName($locale);
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapBrands(ProductFilterData $productFilterData, array $luigisBoxFilter): array
    {
        foreach ($productFilterData->brands as $brand) {
            $luigisBoxFilter[self::FILTER_OR][] = 'brand:' . $brand->getName();
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapParameters(ProductFilterData $productFilterData, array $luigisBoxFilter): array
    {
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if ($parameterFilterData->parameter->isSlider()) {
                $valueFrom = $parameterFilterData->minimalValue === null ? '' : (string)$parameterFilterData->minimalValue;
                $valueTo = $parameterFilterData->maximalValue === null ? '' : (string)$parameterFilterData->maximalValue;

                $luigisBoxFilter[self::FILTER_OR][] = $parameterFilterData->parameter->getName() . ':' . $valueFrom . '|' . $valueTo;
            } else {
                foreach ($parameterFilterData->values as $parameterValue) {
                    $luigisBoxFilter[self::FILTER_OR][] = $parameterFilterData->parameter->getName() . ':' . $parameterValue->getText();
                }
            }
        }

        return $luigisBoxFilter;
    }
}
