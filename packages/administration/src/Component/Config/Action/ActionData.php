<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;

class ActionData
{
    public string $url = 'javascript:void(0)';

    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @param string $cssClass
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface|null $actionRoute
     * @param callable|null $displayIf
     * @param callable(?object $entity): bool|null $displayIf
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ?string $icon,
        public readonly string $cssClass,
        public readonly ?ActionRouteInterface $actionRoute = null,
        public $displayIf = null,
    ) {
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
