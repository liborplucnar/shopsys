<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory as FrameworkCustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as FrameworkCustomerUserUpdateDataFactory;

class CustomerUserUpdateDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        protected readonly FrameworkCustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly BillingAddressDataFactory $billingAddressDataFactory,
        protected readonly FrameworkCustomerUserDataFactory $customerUserDataFactory,
        protected readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUserWithArgument(
        CustomerUser $customerUser,
        Argument $argument,
    ): CustomerUserUpdateData {
        $input = $argument['input'];

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserData = $customerUserUpdateData->customerUserData;

        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value;
            }
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createCompanyFromCustomerUserWithArgument(
        CustomerUser $customerUser,
        Argument $argument,
    ): CustomerUserUpdateData {
        $input = $argument['input'];

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserData = $customerUserUpdateData->customerUserData;
        $billingAddressData = $customerUserUpdateData->billingAddressData;

        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value;
            }

            if (property_exists(get_class($billingAddressData), $key)) {
                $billingAddressData->{$key} = $value;
            }

            $billingAddressData->country = $this->countryFacade->findByCode($input['country']);
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserUpdateData
    {
        return $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData $registrationData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromRegistrationData(RegistrationData $registrationData): CustomerUserUpdateData
    {
        $billingAddressData = $this->billingAddressDataFactory->create();
        $billingAddressData->city = $registrationData->city;
        $billingAddressData->street = $registrationData->street;
        $billingAddressData->postcode = $registrationData->postcode;
        $billingAddressData->country = $registrationData->country;
        $billingAddressData->companyCustomer = $registrationData->companyCustomer;
        $billingAddressData->companyName = $registrationData->companyName;
        $billingAddressData->companyNumber = $registrationData->companyNumber;
        $billingAddressData->companyTaxNumber = $registrationData->companyTaxNumber;
        $billingAddressData->activated = $registrationData->activated;

        $customerUserData = $this->customerUserDataFactory->createForDomainId($registrationData->domainId);
        $customerUserData->createdAt = $registrationData->createdAt;
        $customerUserData->email = $registrationData->email;
        $customerUserData->lastName = $registrationData->lastName;
        $customerUserData->password = $registrationData->password;
        $customerUserData->firstName = $registrationData->firstName;
        $customerUserData->telephone = $registrationData->telephone;
        $customerUserData->newsletterSubscription = $registrationData->newsletterSubscription;
        $customerUserData->sendRegistrationMail = $registrationData->activated;

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->billingAddressData = $billingAddressData;
        $customerUserUpdateData->customerUserData = $customerUserData;

        return $customerUserUpdateData;
    }
}
