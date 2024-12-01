<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

use Shopsys\AdministrationBundle\Component\Config\ActionType;

class CrudActionRouteData implements ActionRouteInterface
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param mixed $id
     * @param callable(?object $entity): int|null $id
     */
    public function __construct(
        private readonly string $crudController,
        private readonly ActionType $actionType,
        private $id,
    ) {
    }

    /**
     * @return class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>
     */
    public function getCrudController(): string
    {
        return $this->crudController;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType
     */
    public function getActionType(): ActionType
    {
        return $this->actionType;
    }

    /**
     * @param object|null $entity
     * @return int|null
     */
    public function getId(?object $entity = null): ?int
    {
        if ($this->id === null) {
            return null;
        }

        if ($entity === null) {
            return call_user_func($this->id);
        }

        return call_user_func($this->id, $entity);
    }
}
