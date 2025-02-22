<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="customer_user_role_groups")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Country\CountryTranslation translation(?string $locale = null)
 */
class CustomerUserRoleGroup extends AbstractTranslatableEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation")
     */
    protected $translations;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    protected $roles;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData $customerUserRoleGroupData
     */
    public function __construct(CustomerUserRoleGroupData $customerUserRoleGroupData)
    {
        $this->translations = new ArrayCollection();
        $this->uuid = $customerUserRoleGroupData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($customerUserRoleGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData $customerUserRoleGroupData
     */
    public function edit(CustomerUserRoleGroupData $customerUserRoleGroupData): void
    {
        $this->setData($customerUserRoleGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData $customerUserRoleGroupData
     */
    protected function setData(CustomerUserRoleGroupData $customerUserRoleGroupData): void
    {
        $this->roles = $customerUserRoleGroupData->roles;
        $this->setTranslations($customerUserRoleGroupData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation
     */
    protected function createTranslation()
    {
        return new CustomerUserRoleGroupTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData $customerUserRoleGroupData
     */
    protected function setTranslations(CustomerUserRoleGroupData $customerUserRoleGroupData)
    {
        foreach ($customerUserRoleGroupData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
