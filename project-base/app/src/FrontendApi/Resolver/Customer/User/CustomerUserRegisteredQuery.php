<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

final class CustomerUserRegisteredQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly BillingAddressRepository $billingAddressRepository,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param string $email
     * @return bool
     */
    private function isCustomerUserRegistered(string $email): bool
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $this->domain->getId());

        return $customerUser !== null;
    }

    /**
     * @param string $companyNumber
     * @return bool
     */
    private function isCompanyRegistered(string $companyNumber): bool
    {
        if (!$this->domain->isB2b()) {
            return false;
        }

        $billingAddress = $this->billingAddressRepository->findByCompanyNumberAndDomainId($companyNumber, $this->domain->getId());

        return $billingAddress !== null;
    }


    /**
     * @param string $email
     * @param string|null $companyNumber
     * @return bool
     */
    public function couldBeCustomerUserRegisteredQuery(string $email, ?string $companyNumber): bool
    {
        return !$this->isCustomerUserRegistered($email) && !$this->isCompanyRegistered($companyNumber);
    }
}
