<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\Order;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\ChoiceField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\DatetimeField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\DomainField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\PriceField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\YesNoField;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

#[CrudController(Order::class)]
class TestController extends AbstractCrudController
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    protected function configure(CrudConfig $config): CrudConfig
    {
        return $config
            //->setTitle(PageType::LIST, 'Test listing')
            //->setMenuTitle(t('Test menu'))
            //->hideInMenu()
            //->setActions([PageType::LIST, PageType::EDIT])
            //->setRoutePrefix('/SomePrefix/prefix-prefixes/hm/')
            ->setMenuSection('customers', 'promo_codes')
            ->disableAction(ActionType::CREATE)
            ;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig
     */
    protected function configureActions(ActionsConfig $actions): ActionsConfig
    {
        $actions->add(
            ActionType::LIST,
            Action::create('linkToDashboard', t('Link To dashboard'))
                ->setIcon('')
                ->setCssClass('btn--primary')
                ->linkToRoute('admin_default_dashboard', fn () => [
                    'id' => 1,
                ]),
        );

        $actions->add(
            ActionType::LIST,
            Action::create('linkToFrontend', t('Link To Frontend'))->linkToUrl(fn () => 'https://www.shopsys.com'),
        );

        $actions->add(
            ActionType::LIST,
            Action::create('testLink2', t('Link 2')),
        );
        $actions->update(
            ActionType::LIST,
            'linkToDashboard',
            fn (AbstractAction $actionBuilder) => $actionBuilder->setLabel('New link'),
        );

        $actions->remove(ActionType::LIST, 'testLink2');

        return $actions;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        $datagrid
            ->addIdentifier('id')
            ->add('number', TextField::class, [
                'label' => t('Order Nr.'),
            ])
            ->add('createdAt', DatetimeField::class, [
                'label' => t('Created at'),
                'type' => 'datetime',
            ])
            ->add('domainId', DomainField::class, [
                'label' => t('Domain ID'),
            ])
            ->add('currency', TextField::class, [
                'visible' => false,
            ])
            ->add('totalPriceWithVat', PriceField::class, [
                'label' => t('Total Price'),
                'withProperty' => 'currency',
            ])
            ->add('customerUser', TextField::class, [
                'label' => t('Customer'),
                'visible' => false,
            ])
            ->add('customerUser.defaultDeliveryAddress', TextField::class, [
                'label' => t('Billing Address'),
                'visible' => false,
            ])
            ->add('customerUser.firstName', TextField::class, [
                'label' => t('First Name'),
            ])
            ->add('deleted', YesNoField::class, [
                'label' => t('Deleted'),
            ])
            ->add('customerUser.customer.id', TextField::class, [
                'label' => t('Customer ID'),
            ])
            ->add('transport.id', ChoiceField::class, [
                'label' => t('Transport'),
                'choices' => [
                    1 => 'Transport 1',
                    2 => 'Transport 2',
                    3 => 'Transport 3',
                    4 => 'Transport 4',
                ],
            ])
        ;

        $datagrid->update('number', [
            'sortable' => false,
        ]);

        $datagrid->remove('customerUser.firstName');

        return $datagrid;
    }
}
