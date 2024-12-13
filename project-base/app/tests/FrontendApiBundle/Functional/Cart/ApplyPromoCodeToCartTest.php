<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Order\PromoCode\PromoCode as AppPromoCode;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Transport\Transport;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ApplyPromoCodeToCartTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private FrontendCustomerUserProvider $frontendCustomerUserProvider;

    /**
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    public function testApplyPromoCode(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);
    }

    public function testApplyPromoCodeForFreeTransport(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::PROMO_CODE_FOR_FREE_TRANSPORT_PAYMENT, 1, AppPromoCode::class);
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $this->domain->getId(), Vat::class);
        $this->addCzechPostToCart();
        $this->addCashOnDeliveryPaymentToCart();

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals($promoCode->getCode(), $data['promoCode']);
        self::assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero), $data['transport']['price']);
        self::assertSame($this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero), $data['payment']['price']);
        self::assertSame($this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('0'), $data['remainingAmountWithVatForFreeTransport']);
    }

    public function testApplyPromoCodeMultipleTimes(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode());

        self::assertEquals(CartDataFixture::CART_UUID, $data['uuid']);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        // apply promo code again
        $response = $this->applyPromoCodeToCart($promoCode->getCode());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertEquals(PromoCode::ALREADY_APPLIED_PROMO_CODE_ERROR, $violations['input.promoCode'][0]['code']);

        // test promo code is applied only once in DB
        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        self::assertCount(1, $cart->getAllAppliedPromoCodes());
    }

    public function testApplyPromoCodeWithInvalidCart(): void
    {
        $invalidCartUuid = '24c11eca-a3f8-45cb-b843-861bcde847c6';

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $response = $this->applyPromoCodeToCart($promoCode->getCode(), $invalidCartUuid);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], 'validators', $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
    }

    public function testApplyPromoCodeWithoutCart(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'promoCode' => $promoCode->getCode(),
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals('validation', $errors[0]['message']);
        self::assertEquals(t('The promo code is not applicable to any of the products in your cart. Check it, please.', [], 'validators', $this->getFirstDomainLocale()), $errors[0]['extensions']['validation']['input.promoCode'][0]['message']);
    }

    public function testModificationAfterProductIsRemoved(): void
    {
        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $productInCart->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $data = $this->applyPromoCodeToCartAndGetResponseData($promoCode->getCode(), $cartUuid);
        self::assertEquals($promoCode->getCode(), $data['promoCode']);

        // product has to be re-fetched due to identity map clearing to prevent "A new entity was found through the relationship" error
        $productInCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);

        $this->hideProduct($productInCart);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => $cartUuid,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $itemModifications = $data['modifications']['itemModifications'];
        $promoCodeModifications = $data['modifications']['promoCodeModifications'];

        self::assertNull($data['promoCode']);

        self::assertNotEmpty($itemModifications['noLongerListableCartItems']);
        self::assertEquals($productInCart->getUuid(), $itemModifications['noLongerListableCartItems'][0]['product']['uuid']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($promoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testModificationAfterPromoCodeEdited(): void
    {
        $validPromoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $data = $this->applyPromoCodeToCartAndGetResponseData($validPromoCode->getCode());

        self::assertEquals($validPromoCode->getCode(), $data['promoCode']);

        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($validPromoCode);
        $promoCodeData->remainingUses = 0;
        $this->promoCodeFacade->edit($validPromoCode->getId(), $promoCodeData);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/GetCart.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        $promoCodeModifications = $data['modifications']['promoCodeModifications'];

        self::assertNull($data['promoCode']);

        self::assertNotEmpty($promoCodeModifications['noLongerApplicablePromoCode']);
        self::assertEquals($validPromoCode->getCode(), $promoCodeModifications['noLongerApplicablePromoCode'][0]);
    }

    public function testPromoCodeIsStillAppliedAfterMergingCart(): void
    {
        $testCartUuid = CartDataFixture::CART_UUID;

        $promoCode = $this->getReferenceForDomain(PromoCodeDataFixture::VALID_PROMO_CODE, 1, AppPromoCode::class);

        $this->applyPromoCodeToCart($promoCode->getCode());

        $response = $this->getResponseContentForGql(__DIR__ . '/../Login/graphql/LoginMutation.graphql', [
            'email' => 'no-reply@shopsys.com',
            'password' => 'user123',
            'cartUuid' => $testCartUuid,
        ]);
        $this->getResponseDataForGraphQlType($response, 'Login');

        $cart = $this->findCartOfCustomerByEmail('no-reply@shopsys.com');

        self::assertNotNull($cart);
        self::assertTrue($cart->isPromoCodeApplied($promoCode->getCode()), 'Promo code have to be applied after merging cart after login');
    }

    /**
     * @param string|null $promoCodeReferenceName
     * @param string $expectedError
     */
    #[DataProvider('getInvalidPromoCodesDataProvider')]
    public function testApplyInvalidPromoCode(?string $promoCodeReferenceName, string $expectedError): void
    {
        $promoCodeCode = 'non-existing-promo-code';

        if ($promoCodeReferenceName !== null) {
            $promoCode = $this->getReferenceForDomain($promoCodeReferenceName, 1, AppPromoCode::class);
            $promoCodeCode = $promoCode->getCode();
        }

        $response = $this->applyPromoCodeToCart($promoCodeCode);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.promoCode', $violations);
        self::assertEquals($expectedError, $violations['input.promoCode'][0]['code']);
    }

    /**
     * @return iterable
     */
    public static function getInvalidPromoCodesDataProvider(): iterable
    {
        yield [null, PromoCode::INVALID_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_PRODUCT_ID_2, PromoCode::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR];

        yield [PromoCodeDataFixture::NOT_YET_VALID_PROMO_CODE, PromoCode::NOT_YET_VALID_ERROR];

        yield [PromoCodeDataFixture::NO_LONGER_VALID_PROMO_CODE, PromoCode::NO_LONGER_VALID_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_REGISTERED_ONLY, PromoCode::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR];

        yield [PromoCodeDataFixture::PROMO_CODE_FOR_VIP_PRICING_GROUP, PromoCode::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR];
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    private function hideProduct(Product $product): void
    {
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->sellingDenied = true;

        $this->productFacade->edit($product->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    private function findCartOfCustomerByEmail(string $email): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($email);

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    private function addCzechPostToCart(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'transportUuid' => $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangeTransportInCart');
    }

    private function addCashOnDeliveryPaymentToCart(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
            'paymentUuid' => $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY, Payment::class)->getUuid(),
        ]);
        $this->getResponseDataForGraphQlType($response, 'ChangePaymentInCart');
    }

    /**
     * @param string $promoCode
     * @param string $cartUuid
     * @return array
     */
    private function applyPromoCodeToCartAndGetResponseData(
        string $promoCode,
        string $cartUuid = CartDataFixture::CART_UUID,
    ): array {
        $response = $this->applyPromoCodeToCart($promoCode, $cartUuid);

        return $this->getResponseDataForGraphQlType($response, 'ApplyPromoCodeToCart');
    }

    /**
     * @param string $promoCode
     * @param string $cartUuid
     * @return array
     */
    private function applyPromoCodeToCart(string $promoCode, string $cartUuid = CartDataFixture::CART_UUID): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => $cartUuid,
            'promoCode' => $promoCode,
        ]);
    }
}
