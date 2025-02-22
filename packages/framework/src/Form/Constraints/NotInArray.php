<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use ArrayAccess;
use Symfony\Component\Validator\Constraint;
use Traversable;

/**
 * @Annotation
 */
class NotInArray extends Constraint
{
    public string $message = 'Value must not be neither of following: {{ array }}';

    public array|Traversable|ArrayAccess $array = [];

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return [
            'array',
        ];
    }
}
