<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JoinNoneEmptyExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('joinNoneEmpty', [$this, 'getArray']),
        ];
    }

    /**
     * @param array $array
     * @param mixed $glue
     * @return string
     */
    public function getArray(array $array, mixed $glue = ', '): string
    {
        return implode($glue, array_filter($array));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'join_none_empty';
    }
}
