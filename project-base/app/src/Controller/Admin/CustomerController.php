<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\CustomerController as BaseCustomerController;

/**
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 * @method __construct(\App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade $customerUserListAdminFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \App\Model\Order\OrderFacade $orderFacade, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrontendApiBundle\Model\Security\LoginAdministratorAsUserUrlProvider $loginAdministratorAsUserUrlProvider, \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade, \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade)
 * @property \Shopsys\FrontendApiBundle\Model\Security\LoginAdministratorAsUserUrlProvider $loginAdministratorAsUserUrlProvider
 * @property \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
 */
class CustomerController extends BaseCustomerController
{
}
