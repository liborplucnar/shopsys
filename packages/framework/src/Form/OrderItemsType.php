<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Admin\Order\OrderItemFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderPaymentFormType;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderTransportFormType;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemsType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly PaymentFacade $paymentFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];

        $payments = $this->paymentFacade->getVisibleByDomainId($order->getDomainId());

        if (!in_array($order->getPayment(), $payments, true)) {
            $payments[] = $order->getPayment();
        }

        $transports = $this->transportFacade->getVisibleByDomainId($order->getDomainId(), $payments);

        if (!in_array($order->getTransport(), $transports, true)) {
            $transports[] = $order->getTransport();
        }

        $builder
            ->add('itemsWithoutTransportAndPayment', CollectionType::class, [
                'entry_type' => OrderItemFormType::class,
                'error_bubbling' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('orderPayment', OrderPaymentFormType::class, [
                'payments' => $payments,
            ])
            ->add('orderTransport', OrderTransportFormType::class, [
                'transports' => $transports,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $options['order'];

        $view->vars['order'] = $order;
        $view->vars['transportPricesWithVatByTransportId'] = $this->transportFacade->getTransportPricesWithVatByCurrencyAndDomainIdIndexedByTransportId(
            $order->getDomainId(),
        );
        $view->vars['transportVatPercentsByTransportId'] = $this->transportFacade->getTransportVatPercentsByDomainIdIndexedByTransportId(
            $order->getDomainId(),
        );
        $view->vars['paymentPricesWithVatByPaymentId'] = $this->paymentFacade->getPaymentPricesWithVatByCurrencyAndDomainIdIndexedByPaymentId(
            $order->getCurrency(),
            $order->getDomainId(),
        );
        $view->vars['paymentVatPercentsByPaymentId'] = $this->paymentFacade->getPaymentVatPercentsByDomainIdIndexedByPaymentId(
            $order->getDomainId(),
        );
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['order'])
            ->addAllowedTypes('order', [Order::class])
            ->setDefaults([
                'inherit_data' => true,
                'render_form_row' => false,
            ]);
    }
}
