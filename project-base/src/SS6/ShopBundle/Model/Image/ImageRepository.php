<?php

namespace SS6\ShopBundle\Model\Image;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Image\Image;

class ImageRepository {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getImageRepository() {
		return $this->em->getRepository(Image::class);
	}

	/**
	 * @param string $entityName
	 * @param int $entityId
	 * @param string|null $type
	 * @return \SS6\ShopBundle\Model\Image\Image|null
	 */
	public function findImageByEntity($entityName, $entityId, $type) {
		$image = $this->getImageRepository()->findOneBy([
				'entityName' => $entityName,
				'entityId' => $entityId,
				'type' => $type,
			],
			['id' => 'asc']
		);

		return $image;
	}

	/**
	 * @param string $entityName
	 * @param int $entityId
	 * @param string|null $type
	 * @return \SS6\ShopBundle\Model\Image\Image
	 */
	public function getImageByEntity($entityName, $entityId, $type) {
		$image = $this->findImageByEntity($entityName, $entityId, $type);
		if ($image === null) {
			$message = 'Image of type "' . ($type ?: 'NULL') . '" not found for entity "' . $entityName . '" with ID ' . $entityId;
			throw new \SS6\ShopBundle\Model\Image\Exception\ImageNotFoundException($message);
		}

		return $image;
	}

	/**
	 * @param string $entityName
	 * @param int $entityId
	 * @param string|null $type
	 * @return \SS6\ShopBundle\Model\Image\Image[]
	 */
	public function getImagesByEntity($entityName, $entityId, $type) {
		return $this->getImageRepository()->findBy([
				'entityName' => $entityName,
				'entityId' => $entityId,
				'type' => $type,
			],
			['id' => 'asc']
		);
	}

	/**
	 * @param string $entityName
	 * @param int $entityId
	 * @return \SS6\ShopBundle\Model\Image\Image[]
	 */
	public function getAllImagesByEntity($entityName, $entityId) {
		return $this->getImageRepository()->findBy([
				'entityName' => $entityName,
				'entityId' => $entityId,
			]
		);
	}

	/**
	 * @param int $imageId
	 * @return \SS6\ShopBundle\Model\Image\Image
	 */
	public function getById($imageId) {
		$image = $this->getImageRepository()->find($imageId);

		if ($image === null) {
			throw new \SS6\ShopBundle\Model\Image\Exception\ImageNotFoundException('Image with ID ' . $imageId . ' does not exist.');
		}

		return $image;
	}

	/**
	 * @param array $entities
	 * @param string $entityName
	 * @return \SS6\ShopBundle\Model\Image\Image[productId]
	 */
	public function getMainImagesByEntitiesIndexedByEntityId(array $entities, $entityName) {
		$queryBuilder = $this->getImageRepository()
			->createQueryBuilder('i')
			->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
			->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entities)
			->orderBy('i.id', 'desc');

		$imagesByProductId = [];
		foreach ($queryBuilder->getQuery()->execute() as $image) {
			/* @var $image \SS6\ShopBundle\Model\Image\Image */
			$imagesByProductId[$image->getEntityId()] = $image;
		}

		return $imagesByProductId;
	}
}
