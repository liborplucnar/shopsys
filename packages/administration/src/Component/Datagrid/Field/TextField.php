<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type TextFieldOptions array{
 *     label?: string|null,
 *     visible?: bool,
 *     template?: string|null,
 *     virtual?: bool,
 *     help?: string|null,
 *     sortable?: bool,
 *  }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField<TextFieldOptions>
 * @template TOptions of array
 */
class TextField extends AbstractField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'sortable' => true,
            'virtual' => false,
            'help' => null,
        ]);

        $optionsResolver->setAllowedTypes('sortable', 'bool');
        $optionsResolver->setAllowedTypes('virtual', 'bool');
        $optionsResolver->setAllowedTypes('help', ['string', 'null']);
    }

    /**
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->options['virtual'];
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->options['help'];
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->options['sortable'];
    }
}
