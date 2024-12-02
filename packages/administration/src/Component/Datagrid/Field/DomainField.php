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
 *  }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField<FieldOptions>
 */
class DomainField extends TextField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'template' => '@ShopsysAdministration/crud/grid/fields/domain.html.twig',
        ]);
    }
}
