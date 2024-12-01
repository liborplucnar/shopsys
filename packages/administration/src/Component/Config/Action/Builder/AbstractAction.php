<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Shopsys\AdministrationBundle\Component\Config\Action\ActionData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;

abstract class AbstractAction
{
    protected ?string $label = null;

    protected ?string $icon = null;

    protected string $cssClass = '';

    protected ?ActionRouteInterface $actionRoute = null;

    /**
     * @var callable|null
     */
    protected $displayIf = null;

    /**
     * @param string $name
     * @param string $label
     * @return $this
     */
    abstract public static function create(string $name, string $label): self;

    /**
     * @param string $name
     * @param string $label
     */
    protected function __construct(protected string $name, string $label)
    {
        $this->label = $label;
    }

    /**
     * Set name of action that will be shown to the users
     *
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon of action that will be shown next to label
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set CSS class that will be added to action button
     *
     * @param string $cssClass
     * @return $this
     */
    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData
     */
    public function getData(): ActionData
    {
        return new ActionData(
            $this->name,
            $this->label,
            $this->icon,
            $this->cssClass,
            $this->actionRoute,
            $this->displayIf,
        );
    }
}
