<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TOptions of array
 */
abstract class AbstractField
{
    /**
     * @var TOptions
     */
    protected array $options;

    /**
     * @param string $name
     * @param TOptions $options
     */
    public function __construct(
        private readonly string $name,
        array $options = [],
    ) {
        $this->options = $this->resolveOptions($options);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'label' => $this->name,
            'visible' => true,
            'template' => '@ShopsysAdministration/crud/grid/fields/basic.html.twig',
        ]);

        $optionsResolver->setAllowedTypes('label', 'string');
        $optionsResolver->setAllowedTypes('visible', 'bool');
        $optionsResolver->setAllowedTypes('template', ['string', 'null']);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->options['label'];
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->options['visible'];
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->options['template'];
    }

    /**
     * @param TOptions $options
     */
    public function update(array $options): void
    {
        $this->options = $this->resolveOptions(array_merge($this->options, $options));
    }

    /**
     * @param TOptions $options
     * @return TOptions
     */
    private function resolveOptions(array $options): array
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        return $optionsResolver->resolve($options);
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareTemplateParameters(): array
    {
        return [];
    }

    /**
     * Normalize value before rendering in template
     *
     * @param mixed $value
     * @param array $row
     * @return mixed
     */
    public function normalize($value, $row): mixed
    {
        return $value;
    }
}
