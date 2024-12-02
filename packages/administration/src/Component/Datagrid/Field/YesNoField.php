<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

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
class YesNoField extends TextField
{
    /**
     * @param bool $value
     * @param array $row
     * @return string
     */
    public function normalize($value, $row): string
    {
        return $value ? 'Yes' : 'No';
    }
}
