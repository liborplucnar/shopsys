<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ProductVisibilityMidnightCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    protected $productVisibilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     */
    public function __construct(ProductVisibilityFacade $productVisibilityFacade)
    {
        $this->productVisibilityFacade = $productVisibilityFacade;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger): void
    {
    }

    public function run(): void
    {
        $this->productVisibilityFacade->refreshProductsVisibility();
    }
}
