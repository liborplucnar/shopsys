<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use LogicException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class CartWithModificationsResult
{
    /**
     * @var array<string, array|bool>
     */
    protected array $cartModifications = [
        'itemModifications' => [],
        'transportModifications' => [],
        'paymentModifications' => [],
        'promoCodeModifications' => [],
        'someProductWasRemovedFromEshop' => false,
    ];

    /**
     * @var array<string, array<int, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem>>
     */
    protected array $itemModifications = [
        'noLongerListableCartItems' => [],
        'cartItemsWithModifiedPrice' => [],
    ];

    /**
     * @var array<string, bool>
     */
    protected array $transportModifications = [
        'transportPriceChanged' => false,
        'transportUnavailable' => false,
        'transportWeightLimitExceeded' => false,
        'personalPickupStoreUnavailable' => false,
    ];

    /**
     * @var array<string, bool>
     */
    protected array $paymentModifications = [
        'paymentPriceChanged' => false,
        'paymentUnavailable' => false,
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $promoCodeModifications = [
        'noLongerApplicablePromoCode' => [],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $multipleAddedProductModifications = [
        'notAddedProducts' => [],
    ];

    protected ?Price $totalPrice = null;

    protected ?Price $totalItemsPrice = null;

    protected ?Price $totalDiscountPrice = null;

    protected ?Price $totalPriceWithoutDiscountTransportAndPayment = null;

    protected ?Money $remainingAmountWithVatForFreeTransport = null;

    protected ?Price $roundingPrice = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function __construct(protected Cart $cart)
    {
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->cart->getCartIdentifier() !== '' ? $this->cart->getCartIdentifier() : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getItems(): array
    {
        return $this->cart->getItems();
    }

    /**
     * @return array<string, array>
     */
    public function getModifications(): array
    {
        $this->cartModifications['itemModifications'] = $this->itemModifications;
        $this->cartModifications['transportModifications'] = $this->transportModifications;
        $this->cartModifications['paymentModifications'] = $this->paymentModifications;
        $this->cartModifications['promoCodeModifications'] = $this->promoCodeModifications;
        $this->cartModifications['multipleAddedProductModifications'] = $this->multipleAddedProductModifications;

        return $this->cartModifications;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerListableCartItem(CartItem $cartItem): void
    {
        $this->itemModifications['noLongerListableCartItems'][] = $cartItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithModifiedPrice(CartItem $cartItem): void
    {
        $this->itemModifications['cartItemsWithModifiedPrice'][] = $cartItem;
    }

    public function setCartHasRemovedProducts(): void
    {
        $this->cartModifications['someProductWasRemovedFromEshop'] = true;
    }

    /**
     * @param bool $transportPriceChanged
     */
    public function setTransportPriceChanged(bool $transportPriceChanged): void
    {
        $this->transportModifications['transportPriceChanged'] = $transportPriceChanged;
    }

    public function setTransportIsUnavailable(): void
    {
        $this->transportModifications['transportUnavailable'] = true;
    }

    /**
     * @param bool $transportWeightLimitExceeded
     */
    public function setTransportWeightLimitExceeded(bool $transportWeightLimitExceeded): void
    {
        $this->transportModifications['transportWeightLimitExceeded'] = $transportWeightLimitExceeded;
    }

    /**
     * @param bool $personalPickupStoreUnavailable
     */
    public function setPersonalPickupStoreUnavailable(bool $personalPickupStoreUnavailable): void
    {
        $this->transportModifications['personalPickupStoreUnavailable'] = $personalPickupStoreUnavailable;
    }

    /**
     * @param bool $paymentPriceChanged
     */
    public function setPaymentPriceChanged(bool $paymentPriceChanged): void
    {
        $this->paymentModifications['paymentPriceChanged'] = $paymentPriceChanged;
    }

    public function setPaymentIsUnavailable(): void
    {
        $this->paymentModifications['paymentUnavailable'] = true;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice(): Price
    {
        if (!$this->totalPrice) {
            throw new LogicException('Total price must be set before calling the getter.');
        }

        return $this->totalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalItemsPrice(): Price
    {
        if (!$this->totalItemsPrice) {
            throw new LogicException('Total items price must be set before calling the getter.');
        }

        return $this->totalItemsPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     */
    public function setTotalPrice(Price $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalItemsPrice
     */
    public function setTotalItemsPrice(Price $totalItemsPrice): void
    {
        $this->totalItemsPrice = $totalItemsPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalDiscountPrice(): Price
    {
        if (!$this->totalDiscountPrice) {
            throw new LogicException('Total discount price must be set before calling the getter.');
        }

        return $this->totalDiscountPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalDiscountPrice
     */
    public function setTotalDiscountPrice(Price $totalDiscountPrice): void
    {
        $this->totalDiscountPrice = $totalDiscountPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getRemainingAmountWithVatForFreeTransport(): ?Money
    {
        return $this->remainingAmountWithVatForFreeTransport;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $remainingAmountWithVatForFreeTransport
     */
    public function setRemainingAmountWithVatForFreeTransport(Money $remainingAmountWithVatForFreeTransport): void
    {
        $this->remainingAmountWithVatForFreeTransport = $remainingAmountWithVatForFreeTransport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public function getTransport(): ?Transport
    {
        return $this->cart->getTransport();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->cart->getPayment();
    }

    /**
     * @return string|null
     */
    public function getPromoCode(): ?string
    {
        if ($this->cart->getFirstAppliedPromoCode() === null) {
            return null;
        }

        return $this->cart->getFirstAppliedPromoCode()->getCode();
    }

    /**
     * @return string|null
     */
    public function getSelectedPickupPlaceIdentifier(): ?string
    {
        return $this->cart->getPickupPlaceIdentifier();
    }

    /**
     * @return string|null
     */
    public function getPaymentGoPayBankSwift(): ?string
    {
        return $this->cart->getPaymentGoPayBankSwift();
    }

    /**
     * @param string $promoCode
     */
    public function addChangedPromoCode(string $promoCode): void
    {
        $this->promoCodeModifications['noLongerApplicablePromoCode'][] = $promoCode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPriceWithoutDiscountTransportAndPayment(): Price
    {
        if (!$this->totalPriceWithoutDiscountTransportAndPayment) {
            throw new LogicException('Total price without discount, transport, and payment must be set before calling the getter.');
        }

        return $this->totalPriceWithoutDiscountTransportAndPayment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPriceWithoutDiscountTransportAndPayment
     */
    public function setTotalPriceWithoutDiscountTransportAndPayment(
        Price $totalPriceWithoutDiscountTransportAndPayment,
    ): void {
        $this->totalPriceWithoutDiscountTransportAndPayment = $totalPriceWithoutDiscountTransportAndPayment;
    }

    /**
     * @return bool
     */
    public function isCartModified(): bool
    {
        return $this->isTransportInCartModified()
            || $this->isPaymentInCartModified()
            || $this->isPromoCodeInCartValid()
            || $this->isSomeCartItemModified()
            || $this->cartModifications['someProductWasRemovedFromEshop'];
    }

    /**
     * @return bool
     */
    protected function isPaymentInCartModified(): bool
    {
        return $this->paymentModifications['paymentPriceChanged']
            || $this->paymentModifications['paymentUnavailable'];
    }

    /**
     * @return bool
     */
    protected function isTransportInCartModified(): bool
    {
        return $this->transportModifications['transportPriceChanged']
            || $this->transportModifications['transportUnavailable']
            || $this->transportModifications['transportWeightLimitExceeded']
            || $this->transportModifications['personalPickupStoreUnavailable'];
    }

    /**
     * @return bool
     */
    protected function isPromoCodeInCartValid(): bool
    {
        return count($this->promoCodeModifications['noLongerApplicablePromoCode']) > 0;
    }

    /**
     * @return bool
     */
    protected function isSomeCartItemModified(): bool
    {
        return count($this->itemModifications['noLongerListableCartItems']) > 0
            || count($this->itemModifications['cartItemsWithModifiedPrice']) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function addProductsNotAddedByMultipleAddition(array $products): void
    {
        foreach ($products as $product) {
            $this->multipleAddedProductModifications['notAddedProducts'][] = $product;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    public function getRoundingPrice(): ?Price
    {
        return $this->roundingPrice->isZero() ? null : $this->roundingPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $roundingPrice
     */
    public function setRoundingPrice(?Price $roundingPrice): void
    {
        $this->roundingPrice = $roundingPrice;
    }
}
