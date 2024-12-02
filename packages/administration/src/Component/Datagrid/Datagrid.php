<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\ActionField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type DatagridOptions from \Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory
 */
final class Datagrid
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField>
     */
    private ArrayCollection $fields;

    private string $identificationName;

    /**
     * @var DatagridOptions
     */
    private array $options;

    /**
     * @param string $entityClass
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridManager $datagridManager
     * @param DatagridOptions $options
     */
    public function __construct(
        private string $entityClass,
        private readonly AdapterInterface $adapter,
        private readonly DatagridManager $datagridManager,
        array $options,
    ) {
        $this->fields = new ArrayCollection();
        $this->options = $this->resolveOptions($options);

        $this->addIdentifier('id');
    }

    /**
     * @param DatagridOptions $options
     * @return DatagridOptions
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'name' => 'datagrid',
            'crudConfig' => null,
        ]);

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('crudConfig', [CrudConfigData::class, 'null']);

        return $resolver->resolve($options);
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function addIdentifier($name): self
    {
        $field = new TextField($name, [
            'label' => t('ID'),
            'sortable' => true,
        ]);

        $this->fields->set($name, $field);
        $this->identificationName = $name;

        return $this;
    }

    /**
     * Define a new field in datagrid
     *
     * @param string $name
     * @param class-string<\Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField> $type
     * @param array<string, mixed> $options
     * @return $this
     */
    public function add(string $name, string $type, array $options = []): self
    {
        Assert::subclassOf($type, AbstractField::class, 'Field type must be instance of AbstractField');

        if ($this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" already exists.', $name));
        }

        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField $field */
        $field = new $type($name, $options);
        $this->fields->set($name, $field);

        return $this;
    }

    /**
     * Update options of field in datagrid
     *
     * @param string $name
     * @param array $options
     */
    public function update(string $name, array $options): void
    {
        if (!$this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" does not exist.', $name));
        }

        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField $field */
        $field = $this->fields->get($name);
        $field->update($options);
    }

    /**
     * Remove field from datagrid
     *
     * @param string $name
     */
    public function remove(string $name): void
    {
        if (!$this->fields->containsKey($name)) {
            throw new InvalidArgumentException(sprintf('Field with name "%s" does not exist.', $name));
        }

        $this->fields->remove($name);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function render(): GridView
    {
        $this->configureDefaultCrudActions();

        $query = $this->adapter->getDatasource($this->entityClass, $this->identificationName, $this->fields->filter(fn (AbstractField $field) => is_a($field, TextField::class))->toArray());
        $grid = $this->datagridManager->createGrid($this->options['name'], $query);

        foreach ($this->fields as $field) {
            if (is_a($field, TextField::class)) {
                if ($field->isVisible() === false) {
                    continue;
                }

                $grid->addColumn($field->getName(), $field->getName(), $field->getLabel(), $field->isSortable(), [
                    'template' => $field->getTemplate(),
                    'help' => $field->getHelp(),
                ]);
            }

            if (!is_a($field, ActionField::class)) {
                continue;
            }

            if ($field->getActionRoute() instanceof RouteActionRouteData) {
                $routeName = $field->getActionRoute()->getRouteName();
                $parameters = $field->getActionRoute()->getRouteParameters();
            } elseif ($field->getActionRoute() instanceof CrudActionRouteData) {
                $routeName = $this->datagridManager->generateRouteName($field->getActionRoute());
                $parameters = ['entityId' => 'o.id'];
            } else {
                throw new InvalidArgumentException('Action route must be instance of RouteActionRouteData or CrudActionRouteData');
            }

            $actionColumn = $grid->addActionColumn($field->getIcon(), $field->getLabel(), $routeName, $parameters);

            if ($field->getConfirmMessage() === true) {
                $actionColumn->setConfirmMessage(t('Are you sure you want to delete this item?'));
            } elseif (is_string($field->getConfirmMessage())) {
                $actionColumn->setConfirmMessage($field->getConfirmMessage());
            }
        }

        $grid->enablePaging();

        return $grid->createView();
    }

    private function configureDefaultCrudActions(): void
    {
        if ($this->options['crudConfig'] === null) {
            return;
        }

        /** @var \Shopsys\AdministrationBundle\Component\Config\CrudConfigData $crudConfig */
        $crudConfig = $this->options['crudConfig'];

        if ($crudConfig->isActionEnabled(ActionType::EDIT) && $this->fields->containsKey('edit') === false) {
            $this->add('edit', ActionField::class, [
                'icon' => 'edit',
                'label' => t('Edit'),
                'crudController' => $crudConfig->getCrudController(),
                'crudAction' => ActionType::EDIT,
            ]);
        }

        if ($crudConfig->isActionEnabled(ActionType::DELETE) && $this->fields->containsKey('delete') === false) {
            $this->add('delete', ActionField::class, [
                'icon' => 'delete',
                'label' => t('Delete'),
                'crudController' => $crudConfig->getCrudController(),
                'crudAction' => ActionType::DELETE,
                'confirmMessage' => true,
            ]);
        }
    }
}
