<?php

namespace Shopsys\FrameworkBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CropZerosExtension extends AbstractExtension
{
    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cropZeros', [$this, 'cropZeros']),
        ];
    }

    /**
     * @param string $value
     * @return string
     */
    public function cropZeros(string $value): string
    {
        return preg_replace('/(?:[,.]0+|([,.]\d*?)0+)$/', '$1', $value);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'cropZeros';
    }
}
