<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Doctrine;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

final class DatagridHydrator extends AbstractHydrator
{
    public const HYDRATION_MODE = 'DatagridHydrator';

    protected function hydrateAllData()
    {
        $result = [];

        while (true) {
            $data = $this->_stmt->fetchAssociative();

            if ($data === false) {
                break;
            }

            $this->hydrateRowData($data, $result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function hydrateRowData(array $data, array &$result): void
    {
        $rowData = $this->gatherGroupedScalarRowData($data);
        $result[] = $rowData;
    }

    /**
     * Copies implementation of gatherScalarRowData(), but groups non-scalar columns
     * as array of columns.
     *
     * @param array $data
     * @return array
     */
    protected function gatherGroupedScalarRowData(&$data)
    {
        $rowData = [];

        foreach ($data as $key => $value) {
            $cacheKeyInfo = $this->hydrateColumnInfo($key);

            if ($cacheKeyInfo === null) {
                continue;
            }

            $fieldName = str_replace('__', '.', $cacheKeyInfo['fieldName']);

            /** @var \Doctrine\DBAL\Types\Type|null $type */
            $type = $cacheKeyInfo['type'];

            if (isset($cacheKeyInfo['isScalar'])) {
                $value = $type->convertToPHPValue($value, $this->_platform);
                $rowData[$fieldName] = $value;
            } else {
                $dqlAlias = $cacheKeyInfo['dqlAlias'];
                $value = $type ? $type->convertToPHPValue($value, $this->_platform) : $value;

                $rowData[$dqlAlias][$fieldName] = $value;
            }
        }

        return $rowData;
    }
}
