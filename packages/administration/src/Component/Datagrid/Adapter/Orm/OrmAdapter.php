<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

final class OrmAdapter implements AdapterInterface
{
    private const DEFAULT_ALIAS = 'o';

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly EntityNameResolver $entityNameResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly Localization $localization,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @param string $identificationName
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\AbstractField> $fields
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    public function getDatasource(string $entityClass, string $identificationName, array $fields): DataSourceInterface
    {
        $proxyQuery = $this->createProxyQuery($entityClass);

        foreach ($fields as $name => $field) {
            if ($field->isVirtual()) {
                continue;
            }

            $proxyQuery->addSelect($name);
        }

        return new DatagridDataSource($proxyQuery->getQueryBuilder(), $identificationName);
    }

    /**
     * @param string $entityClass
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery
     */
    private function createProxyQuery(string $entityClass): ProxyQuery
    {
        $entity = $this->entityNameResolver->resolve($entityClass);

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass($entity);
        $classMetadata = $entityManager->getClassMetadata($entity);

        if (count($classMetadata->getIdentifierFieldNames()) !== 1) {
            throw new RuntimeException('Crud controller does not support entities with composite primary keys.');
        }

        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $entityManager->getRepository($entity);

        return new ProxyQuery($entityClass, $entityManager, $repository->createQueryBuilder(self::DEFAULT_ALIAS), $this->localization->getAdminLocale());
    }
}
