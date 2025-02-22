<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="delivery_addresses")
 * @ORM\Entity
 */
class DeliveryAddress
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\Customer", inversedBy="deliveryAddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customer;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $street;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $city;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $postcode;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function __construct(DeliveryAddressData $deliveryAddressData)
    {
        $this->customer = $deliveryAddressData->customer;
        $this->uuid = $deliveryAddressData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($deliveryAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    public function edit(DeliveryAddressData $deliveryAddressData)
    {
        $this->setData($deliveryAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     */
    protected function setData(DeliveryAddressData $deliveryAddressData): void
    {
        $this->street = $deliveryAddressData->street;
        $this->city = $deliveryAddressData->city;
        $this->postcode = $deliveryAddressData->postcode;
        $this->companyName = $deliveryAddressData->companyName;
        $this->firstName = $deliveryAddressData->firstName;
        $this->lastName = $deliveryAddressData->lastName;
        $this->telephone = $deliveryAddressData->telephone;
        $this->country = $deliveryAddressData->country;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getFullAddress(): string
    {
        return $this->street . ', ' . $this->city . ', ' . $this->postcode;
    }
}
