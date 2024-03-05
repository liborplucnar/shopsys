<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method __construct(\App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory, \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory)
 * @method \App\Model\Order\OrderData create()
 * @method \App\Model\Order\OrderData createFromOrder(\App\Model\Order\Order $order)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @return \App\Model\Order\OrderData
     */
    protected function createInstance(): BaseOrderData
    {
        return new OrderData();
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Order $order
     */
    protected function fillFromOrder(BaseOrderData $orderData, BaseOrder $order): void
    {
        parent::fillFromOrder($orderData, $order);

        $orderData->gtmCoupon = $order->getGtmCoupon();
        $orderData->trackingNumber = $order->getTrackingNumber();
    }
}
