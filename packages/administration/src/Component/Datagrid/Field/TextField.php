<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

/**
 * @phpstan-type TextFieldOptions array{
 *     label?: string|null,
 *     visible?: string|null,
 *     sortable?: boolean,
 *     template?: string|null,
 *     virtual?: string|null,
 *     help?: string|null,
 *     withProperty?: string|null,
 * }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField<\Shopsys\AdministrationBundle\Component\Datagrid\Field\TextFieldOptions>
 */
class TextField extends AbstractField
{
}
