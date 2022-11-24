<?php

namespace Shopsys\FrameworkBundle\Model\Product;

class ProductDeleteResult
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected $productsForRecalculations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $productsForRecalculations
     */
    public function __construct(array $productsForRecalculations = [])
    {
        $this->productsForRecalculations = $productsForRecalculations;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForRecalculations(): array
    {
        return $this->productsForRecalculations;
    }
}
