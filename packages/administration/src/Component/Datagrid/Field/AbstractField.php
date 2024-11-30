<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TODO: Create extended type hints
 *
 * @template-covariant TOptions of array<string, mixed>
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
            'sortable' => true,
            'template' => '@ShopsysAdministration/crud/grid/fields/basic.html.twig',
            'virtual' => false,
            'help' => null,
        ]);

        $optionsResolver->setAllowedTypes('label', 'string');
        $optionsResolver->setAllowedTypes('visible', 'bool');
        $optionsResolver->setAllowedTypes('sortable', 'bool');
        $optionsResolver->setAllowedTypes('template', ['string', 'null']);
        $optionsResolver->setAllowedTypes('virtual', 'bool');
        $optionsResolver->setAllowedTypes('help', ['string', 'null']);
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
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->options['sortable'];
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->options['template'];
    }

    /**
     * @return bool
     */
    public function isVirtual(): bool
    {
        return $this->options['virtual'];
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->options['help'];
    }

    /**
     * @param TOptions $options
     */
    public function update(array $options): void
    {
        $this->options = $this->resolveOptions(array_merge($this->options, $options));
    }

    /**
     * @param array $options
     * @return array
     */
    private function resolveOptions(array $options): array
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        return $optionsResolver->resolve($options);
    }
}
