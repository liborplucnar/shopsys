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
     * @var array<string, mixed>
     */
    protected array $templateParameters = [];

    /**
     * @var callable(mixed $value, mixed $row): mixed|null
     */
    protected $normalize = null;

    /**
     * @param string $id
     * @param string $sourceColumnName
     * @param string $title
     * @param bool $sortable
     * @param mixed $options
     *     template?: string,
     *     help?: string,
     *     templateParameters?: array<string, mixed>,
     *     normalize?: callable(mixed $value, mixed $row): mixed
     * }&array<string, mixed> $options
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
        $this->templateParameters = $options['templateParameters'] ?? [];
        $this->normalize = $options['normalize'] ?? null;
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

    /**
     * @return array<string, mixed>
     */
    public function getTemplateParameters(): array
    {
        return $this->templateParameters;
    }

    /**
     * @param mixed $value
     * @param mixed $row
     * @return mixed
     */
    public function normalizeValue(mixed $value, mixed $row): mixed
    {
        if ($this->normalize !== null) {
            return call_user_func($this->normalize, $value, $row);
        }

        return $value;
    }
}
