<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class Column
{
    protected string $id;

    protected string $sourceColumnName;

    protected string $title;

    protected bool $sortable;

    protected string $classAttribute;

    protected string $orderSourceColumnName;

    protected ?string $template;

    protected ?string $help;

    /**
     * @param string $id
     * @param string $sourceColumnName
     * @param string $title
     * @param bool $sortable
     * @param array{ template?: string, help?: string }&array<string, mixed> $options
     */
    public function __construct($id, $sourceColumnName, $title, $sortable, $options = [])
    {
        $this->id = $id;
        $this->sourceColumnName = $sourceColumnName;
        $this->title = $title;
        $this->sortable = $sortable;
        $this->classAttribute = '';
        $this->orderSourceColumnName = $sourceColumnName;
        $this->template = $options['template'] ?? null;
        $this->help = $options['help'] ?? null;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceColumnName()
    {
        return $this->sourceColumnName;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * @return string
     */
    public function getClassAttribute()
    {
        return $this->classAttribute;
    }

    /**
     * @param string $class
     * @return \Shopsys\FrameworkBundle\Component\Grid\Column
     */
    public function setClassAttribute($class)
    {
        $this->classAttribute = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderSourceColumnName()
    {
        return $this->orderSourceColumnName;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
