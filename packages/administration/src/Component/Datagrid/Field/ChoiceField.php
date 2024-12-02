<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type FieldOptions array{
 *     label?: string|null,
 *     visible?: bool,
 *     template?: string|null,
 *     virtual?: bool,
 *     help?: string|null,
 *     sortable?: bool,
 *     choices?: array<mixed, string>,
 *  }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField<FieldOptions>
 */
class ChoiceField extends TextField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'choices' => null,
        ]);

        $optionsResolver->setAllowedTypes('choices', 'array');
        $optionsResolver->setRequired('choices');
    }

    /**
     * @param mixed $value
     * @param mixed $row
     */
    public function normalize($value, $row): mixed
    {
        return $this->options['choices'][$value] ?? $value;
    }
}
