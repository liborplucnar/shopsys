<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

class RouteActionRouteData implements ActionRouteInterface
{
    /**
     * @var array|callable(?object $entity): array
     */
    public $routeParameters;

    /**
     * @param string $routeName
     * @param array|callable $routeParameters
     * @param array|callable(?object $entity): array $routeParameters
     */
    public function __construct(
        private readonly string $routeName,
        array|callable $routeParameters = [],
    ) {
        $this->routeParameters = $routeParameters;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @param object|null $entity
     * @return array
     */
    public function getRouteParameters(?object $entity = null): array
    {
        if ($entity === null) {
            return is_callable($this->routeParameters) ? call_user_func($this->routeParameters) : $this->routeParameters;
        }

        return is_callable($this->routeParameters) ? call_user_func($this->routeParameters, $entity) : $this->routeParameters;
    }
}
