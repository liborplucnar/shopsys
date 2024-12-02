<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type FieldOptions array{
 *     label?: string|null,
 *     visible?: bool,
 *     template?: string|null,
 *     virtual?: bool,
 *     help?: string|null,
 *     sortable?: bool,
 *     currency?: Currency|null,
 *     withProperty?: string|null,
 *  }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField<FieldOptions>
 */
class PriceField extends TextField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'template' => '@ShopsysAdministration/crud/grid/fields/price.html.twig',
            'currency' => null,
            'withProperty' => null,
        ]);

        $optionsResolver->setAllowedTypes('currency', [Currency::class, 'null']);
        $optionsResolver->setAllowedTypes('withProperty', ['string', 'null']);

        $optionsResolver->setNormalizer('currency', function (OptionsResolver $optionsResolver, $currency) {
            if ($currency === null && $optionsResolver['withProperty'] === null) {
                throw new InvalidOptionsException('One of "currency" or "withProperty" must be set.');
            }

            return $currency;
        });
    }

    /**
     * @return array
     */
    public function prepareTemplateParameters(): array
    {
        if ($this->options['currency'] === null) {
            return [
                'withProperty' => $this->options['withProperty'],
            ];
        }

        return [
            'currency' => $this->options['currency'],
        ];
    }
}
