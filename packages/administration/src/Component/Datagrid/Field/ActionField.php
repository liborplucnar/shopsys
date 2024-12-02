<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type FieldOptions array{
 *     label?: string|null,
 *     visible?: bool,
 *     template?: string|null,
 *     icon?: string,
 *     route?: string|null,
 *     parameters?: array<string, string>,
 *     crudController?: class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>|null,
 *     crudAction?: \Shopsys\AdministrationBundle\Component\Config\ActionType|null,
 *     confirmMessage?: string|bool,
 * }
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField<FieldOptions>
 */
final class ActionField extends AbstractField
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults([
            'icon' => 'question',
            'route' => null,
            'parameters' => [
                'entityId' => 'o.id',
            ],
            'crudController' => null,
            'crudAction' => null,
            'confirmMessage' => false,
        ]);

        $optionsResolver->setAllowedTypes('icon', ['string', 'null']);
        $optionsResolver->setAllowedTypes('route', ['string', 'null']);
        $optionsResolver->setAllowedTypes('parameters', 'array');
        $optionsResolver->setAllowedTypes('crudController', ['string', 'null']);
        $optionsResolver->setAllowedTypes('crudAction', [ActionType::class, 'null']);
        $optionsResolver->setAllowedTypes('confirmMessage', ['string', 'bool']);

        $optionsResolver->setAllowedValues('crudAction', function ($value) {
            if ($value === null) {
                return true;
            }

            return is_a($value, ActionType::class);
        });

        $optionsResolver->setNormalizer('route', function ($options, $route) {
            if ($route === null && $options['crudController'] === null) {
                throw new InvalidOptionsException('At least one of "route" or "crudController" must be set.');
            }

            return $route;
        });

        $optionsResolver->setNormalizer('crudController', function ($options, $crudController) {
            if ($crudController !== null && !is_subclass_of($crudController, AbstractCrudController::class)) {
                throw new InvalidOptionsException('The option "crudController" must be a valid class name that extends AbstractCrudController.');
            }


            if ($crudController !== null && $options['crudAction'] === null) {
                throw new InvalidOptionsException('Both "crudAction" and "crudController" must be set.');
            }

            return $crudController;
        });
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->options['icon'];
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface
     */
    public function getActionRoute(): ActionRouteInterface
    {
        if ($this->options['route'] !== null) {
            return new RouteActionRouteData($this->options['route'], $this->options['parameters']);
        }

        return new CrudActionRouteData($this->options['crudController'], $this->options['crudAction']);
    }

    /**
     * @return string|bool
     */
    public function getConfirmMessage(): string|bool
    {
        return $this->options['confirmMessage'];
    }
}
