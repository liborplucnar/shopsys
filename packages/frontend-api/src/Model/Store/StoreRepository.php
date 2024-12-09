<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Store::class, 's');
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions $storesFilterOptions
     * @return int
     */
    public function getFilteredStoresCount(int $domainId, StoresFilterOptions $storesFilterOptions): int
    {
        $queryBuilder = $this->getBasicFilteredQueryBuilder($domainId, $storesFilterOptions)
            ->select('COUNT(s)');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions $storesFilterOptions
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBasicFilteredQueryBuilder(
        int $domainId,
        StoresFilterOptions $storesFilterOptions,
    ): QueryBuilder {
        $queryBuilder = $this->getQueryBuilder()
            ->andWhere('s.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($storesFilterOptions->getSearchText() !== null) {
            $queryBuilder
                ->andWhere('(normalized(s.city) LIKE normalized(:searchText) OR normalized(s.postcode) LIKE normalized(:searchText))')
                ->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($storesFilterOptions->getSearchText()));
        }

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrontendApiBundle\Model\Store\StoresFilterOptions $storesFilterOptions
     * @param int|null $limit
     * @param int|null $offset
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getFilteredQueryBuilder(
        int $domainId,
        StoresFilterOptions $storesFilterOptions,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        $queryBuilder = $this->getBasicFilteredQueryBuilder($domainId, $storesFilterOptions);
        $queryBuilder->orderBy('s.position, s.id', 'ASC');

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
