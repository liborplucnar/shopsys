<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

class UrlActionRouteData implements ActionRouteInterface
{
    /**
     * @var callable(?object $entity): string
     */
    private $url;

    /**
     * @param callable $url
     * @param callable(?object $entity): string $url
     */
    public function __construct(callable $url)
    {
        $this->url = $url;
    }

    /**
     * @param object|null $entity
     * @return string
     */
    public function getUrl(?object $entity = null): string
    {
        if ($entity === null) {
            return call_user_func($this->url);
        }

        return call_user_func($this->url, $entity);
    }
}
