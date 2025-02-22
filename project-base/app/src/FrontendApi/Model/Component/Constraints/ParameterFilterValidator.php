<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ParameterFilterValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(
        private readonly ParameterFacade $parameterFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\ParameterFilter $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ParameterFilter) {
            throw new UnexpectedTypeException($constraint, ParameterFilter::class);
        }
        $parameterUuid = $value->parameter;
        $parameter = $this->parameterFacade->getByUuid($parameterUuid);

        if ($parameter->isSlider() && count($value->values) > 0) {
            $this->context->buildViolation($constraint->valuesNotSupportedForSliderTypeMessage)
                ->setCode($constraint::VALUES_NOT_SUPPORTED_FOR_SLIDER_TYPE_ERROR)
                ->addViolation();
        }

        if (($value->minimalValue !== null || $value->maximalValue !== null) && $parameter->isSlider() === false) {
            $this->context->buildViolation($constraint->minMaxNotSupportedForNonSliderTypeMessage)
                ->setCode($constraint::MIN_MAX_NOT_SUPPORTED_FOR_NON_SLIDER_TYPE_ERROR)
                ->addViolation();
        }
    }
}
