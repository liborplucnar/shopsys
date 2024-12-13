<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WatchdogValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Watchdog) {
            throw new UnexpectedTypeException($constraint, Watchdog::class);
        }

        if (TransformString::emptyToNull($value) === null) {
            return;
        }

        $productUuid = $value;

        try {
            $product = $this->productFacade->getByUuid($productUuid);

            if ($product->isMainVariant()) {
                $this->addViolationWithCodeToContext($constraint->notAvailableMainVariant, Watchdog::MAIN_VARIANT_ERROR);

                return;
            }

            if ($product->getProductType() === ProductTypeEnum::TYPE_INQUIRY) {
                $this->addViolationWithCodeToContext($constraint->notAvailableInquiry, Watchdog::INQUIRY_ERROR);

                return;
            }
        } catch (ProductNotFoundException) {
            $this->addViolationWithCodeToContext($constraint->productNotFound, Watchdog::PRODUCT_NOT_FOUND_ERROR);
        }
    }

    /**
     * @param string $message
     * @param string $code
     */
    protected function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('watchdog')
            ->addViolation();
    }
}
