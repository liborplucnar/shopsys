<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;

final class DatagridManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     */
    public function __construct(
        private readonly GridFactory $gridFactory,
        private readonly CrudRouteProvider $crudRouteProvider,
    ) {
    }

    /**
     * @param mixed $name
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface $query
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createGrid(mixed $name, DataSourceInterface $query): Grid
    {
        return $this->gridFactory->create($name, $query);
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData $crudActionRouteData
     * @return string
     */
    public function generateRouteName(CrudActionRouteData $crudActionRouteData): string
    {
        return $this->crudRouteProvider->generateCrudRoute($crudActionRouteData->getCrudController(), $crudActionRouteData->getActionType());
    }
}
