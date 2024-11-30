<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\TextField;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Webmozart\Assert\Assert;

/**
 * @template TOptions of array<string, mixed>
 * @template TField of \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField<TOptions>
 */
final class Datagrid
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField>
     */
    private ArrayCollection $fields;

    private string $identificationName;

    /**
     * @param string $entityClass
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface $adapter
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private string $entityClass,
        private readonly AdapterInterface $adapter,
        private readonly GridFactory $gridFactory,
    ) {
        $this->fields = new ArrayCollection();

        $this->addIdentifier('id');
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
     * @param string $name
     * @param class-string<TField> $type
     * @param TOptions $options
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
        $query = $this->adapter->getDatasource($this->entityClass, $this->identificationName, $this->fields->toArray());
        $grid = $this->gridFactory->create('$entityClass', $query);

        foreach ($this->fields as $field) {
            if ($field->isVisible() === false) {
                continue;
            }

            $grid->addColumn($field->getName(), $field->getName(), $field->getLabel(), $field->isSortable(), [
                'template' => $field->getTemplate(),
                'help' => $field->getHelp(),
            ]);
        }

        $grid->enablePaging();

        return $grid->createView();
    }
}
