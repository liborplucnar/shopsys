<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        $email = (string)$value;

        $domainId = $constraint->domainId ?? $this->domain->getId();
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId);

        if ($constraint->ignoredEmail !== $value
            && $customerUser !== null
            && $customerUser->isActivated() === true
        ) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ email }}' => $email,
                ],
            );
        }
    }
}
