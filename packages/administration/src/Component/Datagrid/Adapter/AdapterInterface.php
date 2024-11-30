<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

interface AdapterInterface
{
    /**
     * @param class-string $entityClass
     * @param string $identificationName
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField[] $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    public function getDatasource(string $entityClass, string $identificationName, array $fields): DataSourceInterface;
}
