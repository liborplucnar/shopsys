<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderLastNameFilter implements AdvancedSearchFilterInterface
{
    public const NAME = 'customerLastName';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormType(): string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, $rulesData): void
    {
        foreach ($rulesData as $index => $ruleData) {
            if ($ruleData->value === null || $ruleData->value === '') {
                $searchValue = '%';
            } else {
                $searchValue = DatabaseSearching::getLikeSearchString($ruleData->value) . '%';
            }
            $dqlOperator = $this->getContainsDqlOperator($ruleData->operator);
            $parameterName = 'lastName_' . $index;
            $queryBuilder->andWhere(
                'NORMALIZE(o.lastName) ' . $dqlOperator . ' NORMALIZE(:' . $parameterName . ') OR NORMALIZE(o.deliveryLastName) ' . $dqlOperator . ' NORMALIZE(:' . $parameterName . ')'
            );
            $queryBuilder->setParameter($parameterName, $searchValue);
        }
    }

    /**
     * @param string $operator
     * @return string
     */
    protected function getContainsDqlOperator(string $operator): string
    {
        switch ($operator) {
            case self::OPERATOR_CONTAINS:
                return 'LIKE';
            case self::OPERATOR_NOT_CONTAINS:
                return 'NOT LIKE';
        }

        throw new AdvancedSearchFilterOperatorNotFoundException($operator);
    }
}
