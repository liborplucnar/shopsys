<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;

/**
 * @phpstan-type DatagridOptions array{
 *     name?: string,
 *     crudConfig?: \Shopsys\AdministrationBundle\Component\Config\CrudConfigData|null,
 *     pagination?: bool,
 * }
 */
final class DatagridFactory
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridManager $datagridManager
     */
    public function __construct(
        private readonly DatagridManager $datagridManager,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param DatagridOptions $options
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function create(string $entityClass, AdapterInterface $adapter, ?array $options = []): Datagrid
    {
        $datagrid = new Datagrid($entityClass, $adapter, $this->datagridManager, $options);

        return $datagrid;
    }
}
