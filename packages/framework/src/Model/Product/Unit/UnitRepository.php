<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Unit\Exception\UnitNotFoundException;

class UnitRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getUnitRepository()
    {
        return $this->em->getRepository(Unit::class);
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     */
    public function findById($unitId)
    {
        return $this->getUnitRepository()->find($unitId);
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getById($unitId)
    {
        $unit = $this->findById($unitId);

        if ($unit === null) {
            throw new UnitNotFoundException('Unit with ID ' . $unitId . ' not found.');
        }

        return $unit;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('u, ut')
            ->from(Unit::class, 'u')
            ->join('u.translations', 'ut')
            ->orderBy('u.id');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAll()
    {
        return $this->getAllQueryBuilder()->getQuery()->execute();
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAllExceptId($unitId)
    {
        return $this->getAllQueryBuilder()
            ->where('u.id != :id')->setParameter('id', $unitId)
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @return bool
     */
    public function existsProductWithUnit(Unit $unit)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(p)')
            ->from(Product::class, 'p')
            ->where('p.unit = :unit')->setParameter('unit', $unit);

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return bool
     */
    public function isAtLeastOneUnitCreated(): bool
    {
        return $this->getUnitRepository()->count([]) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $oldUnit
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $newUnit
     */
    public function replaceUnit(Unit $oldUnit, Unit $newUnit)
    {
        $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.unit', ':newUnit')->setParameter('newUnit', $newUnit)
            ->where('p.unit = :oldUnit')->setParameter('oldUnit', $oldUnit)
            ->getQuery()->execute();
    }
}
