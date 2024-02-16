<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Scope;

use Webmozart\Assert\Assert;

class ExportScopeRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    protected array $allScopes;

    /**
     * @param iterable $allScopes
     */
    public function __construct(
        iterable $allScopes,
    ) {
        $this->registerScopes($allScopes);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    public function getAllScopes(): array
    {
        return $this->allScopes;
    }

    /**
     * @param string[] $propertyNames
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    public function getScopesByPropertyNames(array $propertyNames): array
    {
        $scopes = [];
        foreach ($this->allScopes as $scope) {
            if (array_intersect($scope->getEntityFieldNames(), $propertyNames) !== []) {
                $scopes[] = $scope;
            }
        }
// TODO na nějaké úrovni bych měl vrátit všechny pokud jsem žádný nenašel (případně můžu teda vytvořit jen All? ale jaké bych zvolil klíče?)
        return $scopes;
    }

    /**
     * @param iterable $allScopes
     */
    protected function registerScopes(iterable $allScopes): void
    {
        Assert::allIsInstanceOf($allScopes, ExportScopeInterface::class);

        foreach ($allScopes as $scope) {
            $this->allScopes[] = $scope;
        }
    }
}
