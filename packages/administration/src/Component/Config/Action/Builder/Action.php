<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;

final class Action extends AbstractAction
{
    /**
     * @param string $name
     * @param string $label
     * @return self
     */
    public static function create(string $name, string $label): self
    {
        return new self($name, $label);
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param callable $function
     * @param callable(?object $entity): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(callable $function): self
    {
        $this->displayIf = $function;

        return $this;
    }

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or callable function that will return array.
     *
     * @param string $route
     * @param array|callable $parameters
     * @param array|callable(?object $entity): array $parameters
     * @return $this
     */
    public function linkToRoute(string $route, array|callable $parameters = []): self
    {
        $this->actionRoute = new RouteActionRouteData($route, $parameters);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * Url is provided by a callable function that will return string.
     *
     * @param callable $url
     * @param callable(?object $entity): string $url
     * @return $this
     */
    public function linkToUrl(callable $url): self
    {
        $this->actionRoute = new UrlActionRouteData($url);

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     * If you are linking to page type that requires entity ID, you must provide callable function that will return entity ID.
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param callable|null $id
     * @param null|callable(?object $entity): int $id
     * @return $this
     */
    public function linkToCrud(string $crudController, ActionType $actionType, ?callable $id = null): self
    {
        $this->actionRoute = new CrudActionRouteData($crudController, $actionType, $id);

        return $this;
    }
}
