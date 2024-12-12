<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use App\DataFixtures\Demo\PriceListDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceFactory;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\App\Test\TransactionFunctionalTestCase;

final class EffectiveSpecialPriceForProductTest extends TransactionFunctionalTestCase
{
    private const int TEST_DOMAIN_ID = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade
     * @inject
     */
    private readonly SpecialPriceFacade $specialPriceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceDataFactory
     * @inject
     */
    private readonly ProductWithPriceDataFactory $productWithPriceDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\ProductWithPriceFactory
     * @inject
     */
    private readonly ProductWithPriceFactory $productWithPriceFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory
     * @inject
     */
    private readonly PriceListDataFactory $priceListDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade
     * @inject
     */
    private readonly PriceListFacade $priceListFacade;

    public function testNoSpecialPriceIsReturnedWhenNotSet(): void
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);

        $this->assertNull($specialPrice, 'No special price should be set for product');
    }

    public function testSpecialPriceIsReturnedForActiveList(): void
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $priceAmount = '10';

        $this->addProductToPriceList($helloKittyProduct, PriceListDataFixture::ACTIVE_SPECIAL_OFFERS_REFERENCE, $priceAmount);

        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);

        $this->assertNotNull($specialPrice, 'Special price should be set for product');
        $this->assertTrue($specialPrice->isNowActive(), 'Special price should be active');
        $this->assertFalse($specialPrice->isFuturePrice(), 'Special price should not be future price');
        $this->assertMoney($priceAmount, $specialPrice->price->getPriceWithVat());
    }

    public function testProperSpecialPriceIsReturnedForMultipleLists(): void
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $firstActivePriceAmount = '20';
        $secondActivePriceAmount = '10';

        $this->addProductToPriceList($helloKittyProduct, PriceListDataFixture::ACTIVE_SPECIAL_OFFERS_REFERENCE, $firstActivePriceAmount);
        $this->addProductToPriceList($helloKittyProduct, PriceListDataFixture::ACTIVE_ITEMS_ON_SALE_REFERENCE, $secondActivePriceAmount);

        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);
        $this->assertNotNull($specialPrice, 'Special price should be set for product');
        $this->assertTrue($specialPrice->isNowActive(), 'Special price should be active');
        $this->assertFalse($specialPrice->isFuturePrice(), 'Special price should not be future price');
        $this->assertMoney($firstActivePriceAmount, $specialPrice->price->getPriceWithVat());

        // update the price list to increase its priority
        $priceList = $this->getReferenceForDomain(PriceListDataFixture::ACTIVE_ITEMS_ON_SALE_REFERENCE, self::TEST_DOMAIN_ID, PriceList::class);
        $priceListData = $this->priceListDataFactory->createFromPriceList($priceList);
        $this->priceListFacade->edit($priceList->getId(), $priceListData);

        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);
        $this->assertNotNull($specialPrice, 'Special price should be set for product');
        $this->assertTrue($specialPrice->isNowActive(), 'Special price should be active');
        $this->assertFalse($specialPrice->isFuturePrice(), 'Special price should not be future price');
        $this->assertMoney($secondActivePriceAmount, $specialPrice->price->getPriceWithVat());
    }

    public function testSpecialPriceIsReturnedForFutureList(): void
    {
        $priceAmount = '10';
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $this->addProductToPriceList($helloKittyProduct, PriceListDataFixture::FUTURE_PROMOTED_PRODUCTS_REFERENCE, $priceAmount);

        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);

        $this->assertNotNull($specialPrice, 'Special price should be set for product');
        $this->assertFalse($specialPrice->isNowActive(), 'Special price should not be active');
        $this->assertTrue($specialPrice->isFuturePrice(), 'Special price should be future price');
        $this->assertMoney($priceAmount, $specialPrice->price->getPriceWithVat());
    }

    public function testNoSpecialPriceIsReturnedForExpiredList(): void
    {
        $helloKittyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $this->addProductToPriceList($helloKittyProduct, PriceListDataFixture::EXPIRED_BLUE_WEDNESDAY_REFERENCE, '10');

        $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($helloKittyProduct, self::TEST_DOMAIN_ID);

        $this->assertNull($specialPrice, 'No special price should be set for product');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $priceListReferenceName
     * @param string $productPriceAmount
     */
    public function addProductToPriceList(
        Product $product,
        string $priceListReferenceName,
        string $productPriceAmount,
    ): void {
        $priceList = $this->getReferenceForDomain($priceListReferenceName, self::TEST_DOMAIN_ID, PriceList::class);

        $productWithPriceData = $this->productWithPriceDataFactory->create(
            $product,
            Money::create($productPriceAmount),
            self::TEST_DOMAIN_ID,
        );

        $productWithPrice = $this->productWithPriceFactory->create($productWithPriceData);

        $priceList->addProductWithPrice($productWithPrice);

        $this->em->flush();
    }
}
