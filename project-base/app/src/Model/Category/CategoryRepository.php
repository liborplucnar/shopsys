<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Category\LinkedCategory\LinkedCategory;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository as BaseCategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;

/**
 * @method \App\Model\Category\Category[] getAll()
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category[] getFullPathsIndexedByIdsForDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category getRootCategory()
 * @method \App\Model\Category\Category|null findById(int $categoryId)
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getOneByUuid(string $uuid)
 * @method \App\Model\Category\Category[] getPreOrderTreeTraversalForAllCategories(string $locale)
 * @method \App\Model\Category\Category[] getPreOrderTreeTraversalForVisibleCategoriesByDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getTranslatedVisibleSubcategoriesByDomain(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getVisibleByDomainIdAndSearchText(int $domainId, string $locale, string|null $searchText)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryOnDomain(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category getProductMainCategoryOnDomain(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getCategoriesByIds(int[] $categoryIds)
 * @method \App\Model\Category\Category[] getCategoriesWithVisibleChildren(\App\Model\Category\Category[] $categories, int $domainId)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository)
 * @method \App\Model\Category\Category[] getAllTranslatedWithoutBranch(\App\Model\Category\Category $categoryBranch, string $locale)
 * @method \App\Model\Category\Category[] getAllTranslated(string $locale)
 */
class CategoryRepository extends BaseCategoryRepository
{
    /**
     * @param \App\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return int[]
     */
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        $domainId,
    ) {
        if (count($categories) === 0) {
            return [];
        }
        $listableProductCountsIndexedByCategoryId = [];

        foreach ($categories as $category) {
            // Initialize array with zeros as categories without found products will not be represented in result rows
            $listableProductCountsIndexedByCategoryId[$category->getId()] = 0;
        }

        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->join(
            ProductCategoryDomain::class,
            'pcd',
            Join::WITH,
            'pcd.product = p
                 AND pcd.category IN (:categories)
                 AND pcd.domainId = :domainId',
        )
            ->select('IDENTITY(pcd.category) AS categoryId, COUNT(p) AS productCount')
            ->setParameter('categories', $categories)
            ->setParameter('domainId', $domainId)
            ->groupBy('pcd.category')
            ->resetDQLPart('orderBy');

        $results = $queryBuilder->getQuery()->getArrayResult();

        foreach ($results as $result) {
            $listableProductCountsIndexedByCategoryId[$result['categoryId']] = $result['productCount'];
        }

        return $listableProductCountsIndexedByCategoryId;
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getFullPathsIndexedByIds(string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesQueryBuilder($locale);

        $rows = $queryBuilder->select('c.id, IDENTITY(c.parent) AS parentId, ct.name')->getQuery()->getScalarResult();

        $fullPathsById = [];

        foreach ($rows as $row) {
            if (array_key_exists($row['parentId'], $fullPathsById)) {
                $fullPathsById[$row['id']] = $fullPathsById[$row['parentId']] . ' - ' . $row['name'];
            } else {
                $fullPathsById[$row['id']] = $row['name'];
            }
        }

        return $fullPathsById;
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getPreOrderTreeTraversalForAllCategoriesQueryBuilder(string $locale): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->andWhere('c.level >= 1')
            ->orderBy('c.lft');

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @param int $domainId
     * @param \App\Model\Category\Category[] $excludeCategories
     * @return \App\Model\Category\Category[]
     */
    public function getVisibleCategoriesByLinkedCategories(
        Category $parentCategory,
        int $domainId,
        array $excludeCategories,
    ): array {
        $excludeCategories[] = $parentCategory;

        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join(LinkedCategory::class, 'lc', Join::WITH, 'lc.category = c AND lc.parentCategory = :parentCategory')
            ->andWhere('c NOT IN (:excludeCategories)')
            ->orderBy('lc.position', 'asc')
            ->setParameter('excludeCategories', $excludeCategories)
            ->setParameter('parentCategory', $parentCategory);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string|null $searchText
     * @param int $domainId
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultForSearchVisible(
        $searchText,
        $domainId,
        $locale,
        $page,
        $limit,
    ): PaginationResult {
        $queryBuilder = $this->getVisibleByDomainIdAndSearchTextQueryBuilder($domainId, $locale, $searchText);
        $queryBuilder->orderBy(OrderByCollationHelper::createOrderByForLocale('ct.name', $locale));

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }

    /**
     * Thanks to joining "c.domains" instead of "CategoryDomain::class",
     * the category domains can be eager loaded (by adding "cd" to "select" part), but are still excluded from the result array
     *
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllVisibleByDomainIdQueryBuilder($domainId)
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->join('c.domains', 'cd', Join::WITH, 'cd.domainId = :domainId AND cd.visible = TRUE');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainConfig(
        Category $category,
        DomainConfig $domainConfig,
    ): array {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId())
            ->addSelect('cd')
            ->andWhere('c.parent = :category')
            ->setParameter('category', $category);
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        return $queryBuilder->getQuery()->execute();
    }
}
