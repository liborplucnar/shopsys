<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

final class DatagridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        public readonly GridFactory $gridFactory,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function create(string $entityClass, AdapterInterface $adapter): Datagrid
    {
        $datagrid = new Datagrid($entityClass, $adapter, $this->gridFactory);

        return $datagrid;
    }
}
