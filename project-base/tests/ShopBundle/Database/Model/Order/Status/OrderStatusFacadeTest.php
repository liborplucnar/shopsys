<?php

namespace Tests\ShopBundle\Database\Model\Order\Status;

use Shopsys\ShopBundle\DataFixtures\Base\OrderStatusDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\OrderDataFixture;
use Shopsys\ShopBundle\Model\Order\OrderData;
use Shopsys\ShopBundle\Model\Order\OrderFacade;
use Shopsys\ShopBundle\Model\Order\Status\OrderStatusData;
use Shopsys\ShopBundle\Model\Order\Status\OrderStatusFacade;
use Tests\ShopBundle\Test\DatabaseTestCase;

class OrderStatusFacadeTest extends DatabaseTestCase
{
    public function testDeleteByIdAndReplace()
    {
        $em = $this->getEntityManager();
        $orderStatusFacade = $this->getServiceByType(OrderStatusFacade::class);
        /* @var $orderStatusFacade \Shopsys\ShopBundle\Model\Order\Status\OrderStatusFacade */
        $orderFacade = $this->getServiceByType(OrderFacade::class);
        /* @var $orderFacade \Shopsys\ShopBundle\Model\Order\OrderFacade */

        $orderStatusToDelete = $orderStatusFacade->create(new OrderStatusData(['cs' => 'name']));
        $orderStatusToReplaceWith = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        /* @var $orderStatusToReplaceWith \Shopsys\ShopBundle\Model\Order\Status\OrderStatus */
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1');
        /* @var $order \Shopsys\ShopBundle\Model\Order\Order */

        $orderData = new OrderData();
        $orderData->setFromEntity($order);
        $orderData->status = $orderStatusToDelete;
        $orderFacade->edit($order->getId(), $orderData);

        $orderStatusFacade->deleteById($orderStatusToDelete->getId(), $orderStatusToReplaceWith->getId());

        $em->refresh($order);

        $this->assertEquals($orderStatusToReplaceWith, $order->getStatus());
    }
}
