<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

final class CompanyRegisteredQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly BillingAddressRepository $billingAddressRepository,
    ) {
    }

    /**
     * @param string $companyNumber
     * @return bool
     */
    public function isCompanyRegisteredQuery(string $companyNumber): bool
    {
        if (!$this->domain->isB2b()) {
            return false;
        }

        $billingAddress = $this->billingAddressRepository->findByCompanyNumberAndDomainId($companyNumber, $this->domain->getId());

        return $billingAddress !== null;
    }
}
