<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EmailValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Email) {
            throw new UnexpectedTypeException($constraint, Email::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string)$value;

        if (!$this->isEmail($value)) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * @see https://doc.nette.org/cs/2.4/validators
     * @param string $value
     * @return bool
     */
    protected function isEmail($value): bool
    {
        $atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
        $alpha = "a-z\x80-\xFF"; // superset of IDN

        return (bool)preg_match('(^
            ("([ !#-[\\]-~]*|\\\\[ -~])+"|' . $atom . '+(\\.' . $atom . '+)*) # quoted or unquoted
            @
            ([0-9' . $alpha . ']([-0-9' . $alpha . ']{0,61}[0-9' . $alpha . '])?\\.)+ # domain - RFC 1034
            [' . $alpha . ']([-0-9' . $alpha . ']{0,17}[' . $alpha . '])? # top domain
            \\z)ix', $value);
    }
}
