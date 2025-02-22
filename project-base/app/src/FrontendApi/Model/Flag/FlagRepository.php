<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Flag;

use App\Model\Product\Flag\Flag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class FlagRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param int[][] $flagsIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Product\Flag\Flag[][]
     */
    public function getFlagsByIds(array $flagsIds, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id IN (:flagsIds)')
            ->andWhere('f.visible = true')
            ->indexBy('f', 'f.id')
            ->setParameter('flagsIds', array_merge(...$flagsIds))
            ->setParameter('locale', $domainConfig->getLocale());
        $result = $queryBuilder->getQuery()->execute();

        $allFlags = [];

        foreach ($flagsIds as $key => $flagIds) {
            $allFlags[$key] = [];

            foreach ($flagIds as $flagId) {
                if (!array_key_exists($flagId, $result)) {
                    continue;
                }

                $allFlags[$key][$flagId] = $result[$flagId];
            }
        }

        return $this->sortFlagsById($allFlags);
    }

    /**
     * @param \App\Model\Product\Flag\Flag[][] $flagsIndexedByKeyAndId
     * @return \App\Model\Product\Flag\Flag[][]
     */
    private function sortFlagsById(array $flagsIndexedByKeyAndId): array
    {
        $allFlagsValues = [];

        foreach ($flagsIndexedByKeyAndId as $flagsIndexedById) {
            ksort($flagsIndexedById);
            $allFlagsValues[] = array_values($flagsIndexedById);
        }

        return $allFlagsValues;
    }
}
