<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

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
 *     type?: "datetime"|"date"|"time",
 *  }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField<FieldOptions>
 */
class DatetimeField extends TextField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'template' => '@ShopsysAdministration/crud/grid/fields/datetime.html.twig',
            'type' => 'datetime',
        ]);

        $optionsResolver->setAllowedTypes('type', 'string');

        $optionsResolver->setNormalizer('type', function (OptionsResolver $optionsResolver, $type) {
            if (!in_array($type, ['datetime', 'date', 'time'], true)) {
                throw new InvalidOptionsException('Type must be one of "datetime", "date" or "time".');
            }

            return $type;
        });
    }

    /**
     * @return array
     */
    public function prepareTemplateParameters(): array
    {
        return [
            'type' => $this->options['type'],
        ];
    }
}
