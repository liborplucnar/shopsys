<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsFactory;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCrudController extends AbstractController
{
    private ?CrudConfigData $config = null;

    private ?ActionsConfig $actions = null;

    #[Required]
    public ActionsFactory $actionsFactory;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    protected function configure(CrudConfig $config): CrudConfig
    {
        return $config;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig
     */
    protected function configureActions(ActionsConfig $actions): ActionsConfig
    {
        return $actions;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('@ShopsysAdministration/crud/list.html.twig', [
            'title' => $this->getConfig()->getTitle(PageType::LIST),
            'globalActions' => $this->actionsFactory->processActions($this->getConfiguredActions(PageType::LIST), ActionType::GLOBAL),
        ]);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionBuilder[]
     */
    private function getConfiguredActions(PageType $pageType): array
    {
        if ($this->actions === null) {
            $this->actions = $this->configureActions(new ActionsConfig(get_class($this), $this->getConfig()->getDefaultActions()));
        }

        return $this->actions->getActions($pageType);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    final public function getConfig(): CrudConfigData
    {
        if ($this->config === null) {
            $reflectionClass = new ReflectionClass($this);
            $attributes = $reflectionClass->getAttributes(CrudController::class);

            if (count($attributes) === 0) {
                throw new \RuntimeException(sprintf('Class %s must have @%s attribute.', $reflectionClass->getName(), CrudController::class));
            }

            $entityClass = $attributes[0]->newInstance()->entityClass;
            $entityName = (new ReflectionClass($entityClass))->getShortName();

            $this->config = $this->configure(new CrudConfig($entityName))->getConfig();
        }

        return $this->config;
    }
}
