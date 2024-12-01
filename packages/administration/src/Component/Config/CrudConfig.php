<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Webmozart\Assert\Assert;

final class CrudConfig
{
    private CrudConfigData $crudConfigData;

    /**
     * @param string $entityClass
     */
    public function __construct(string $entityClass)
    {
        $this->crudConfigData = new CrudConfigData($entityClass);
    }

    /**
     * Sets a custom title for a given page type.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param string $title
     * @return $this
     */
    public function setTitle(PageType $pageType, string $title): self
    {
        $this->crudConfigData->customPageTitles[$pageType->value] = $title;

        return $this;
    }

    /**
     * Sets the title of the menu item that will be used.
     *
     * @param string $menuTitle
     * @return $this
     */
    public function setMenuTitle(string $menuTitle): self
    {
        $this->crudConfigData->menuTitle = $menuTitle;

        return $this;
    }

    /**
     * Sets which default actions are enabled for the crud controller.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType[] $actions
     * @return $this
     */
    public function setActions(array $actions): self
    {
        Assert::allIsInstanceOf($actions, PageType::class, 'The given action is not a valid page type');
        $this->crudConfigData->defaultActions = $actions;

        return $this;
    }

    /**
     * TODO: Do something with $menuSection to be able to use some Enum instead of string
     *
     * Sets where the crud controller will be displayed in the side menu.
     *
     * @param string $menuSection Name of root level menu section
     * @param string|null $submenuSection Name of submenu section
     * @return $this
     */
    public function setMenuSection(string $menuSection, ?string $submenuSection = null): self
    {
        $this->crudConfigData->menuSection = $menuSection;
        $this->crudConfigData->submenuSection = $submenuSection;

        return $this;
    }

    /**
     * Hides the CRUD controller in the side menu.
     *
     * @return $this
     */
    public function hideInMenu(): self
    {
        $this->crudConfigData->visibleInMenu = false;

        return $this;
    }

    /**
     * Set custom route prefix for the CRUD controller. This will be used as a prefix for all routes
     *
     * @param string|null $routePrefix
     * @return $this
     */
    public function setRoutePrefix(?string $routePrefix): self
    {
        $this->crudConfigData->routePrefix = $routePrefix;

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    public function getConfig(): CrudConfigData
    {
        return $this->crudConfigData;
    }
}
