<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CrudMenuSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider $crudConfigProvider
     */
    public function __construct(
        public readonly CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry,
        public readonly CrudRouteProvider $crudRouteProvider,
        public readonly CrudConfigProvider $crudConfigProvider,
    ) {
    }

    // TODO: This will not work for newly created Root sections in project-base
    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_ROOT => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_DASHBOARD => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_ORDERS => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_CUSTOMERS => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_PRODUCTS => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_PRICING => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_MARKETING => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_ADMINISTRATORS => 'onConfigureMenu',
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'onConfigureMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function onConfigureMenu(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();

        foreach ($this->crudControllerDefinitionRegistry->getItems() as $item) {
            $config = $this->crudConfigProvider->getConfig($item);

            $sectionMenu = $config->getMenuSection();
            $submenuSection = $config->getSubmenuSection();

            if ($menu->getName() !== $sectionMenu) {
                continue;
            }

            // TODO: Generate routes for other pages and maybe for custom actions as well
            $route = $this->crudRouteProvider->generate($item, PageType::LIST);

            if ($submenuSection !== null) {
                $menu = $menu->getChild($submenuSection);
            }

            $menu->addChild($route->getRouteName(), [
                'route' => $route->getRouteName(),
                'display' => $config->isVisibleInMenu(),
                'label' => $config->getMenuTitle(),
            ]);
        }
    }
}
