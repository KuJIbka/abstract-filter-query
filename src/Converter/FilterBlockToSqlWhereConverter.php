<?php


namespace AFQ\Converter;

use AFQ\Block\AndFilterBlock;
use AFQ\Block\AbstractFilterBlock;
use AFQ\Block\OrFilterBlock;
use AFQ\Comparison\AbstractOperation;
use AFQ\Comparison\Between;
use AFQ\Comparison\Equal;
use AFQ\Comparison\Greater;
use AFQ\Comparison\GreaterEqual;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\Less;
use AFQ\Comparison\LessEqual;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Sorting\Sorting;

class FilterBlockToSqlWhereConverter extends AbstractConverter
{
    protected function convertBlock(AbstractFilterBlock $abstractFilterBlock): string
    {
        $result = '';
        $partsAsString = [];
        foreach ($abstractFilterBlock->getSubBlocks() as $subBlock) {
            if ($subBlock instanceof AbstractFilterBlock) {
                $partsAsString[] = $this->convertBlock($subBlock);
            } elseif ($subBlock instanceof AbstractOperation) {
                $partsAsString[] = $this->convertOperation($subBlock);
            }
        }

        if ($abstractFilterBlock instanceof AndFilterBlock) {
            $result = '(' . implode(' AND ', $partsAsString) . ')';
        } elseif ($abstractFilterBlock instanceof OrFilterBlock) {
            $result = '(' . implode(' OR ', $partsAsString) . ')';
        }

        return $result;
    }

    protected function convertOperation(AbstractOperation $abstractOperation): string
    {
        $className = get_class($abstractOperation);
        switch ($className) {
            case Equal::class:
                /** @var Equal $abstractOperation */
                return $abstractOperation->getKey() . '=' . $this->convertValue($abstractOperation->getValue());
            case Greater::class:
                /** @var Greater $abstractOperation */
                return $abstractOperation->getKey() . '>' . $this->convertValue($abstractOperation->getValue());
            case GreaterEqual::class:
                /** @var GreaterEqual $abstractOperation */
                return $abstractOperation->getKey() . '>=' . $this->convertValue($abstractOperation->getValue());
            case Less::class:
                /** @var Less $abstractOperation */
                return $abstractOperation->getKey() . '<' . $this->convertValue($abstractOperation->getValue());
            case LessEqual::class:
                /** @var LessEqual $abstractOperation */
                return $abstractOperation->getKey() . '<=' . $this->convertValue($abstractOperation->getValue());
            case In::class:
                /** @var In $abstractOperation */
                return $abstractOperation->getKey()
                    . ' IN (' . $this->convertValue($abstractOperation->getValue()) . ')';
            case NotIn::class:
                /** @var NotIn $abstractOperation */
                return $abstractOperation->getKey() . ' NOT IN ('
                    . $this->convertValue($abstractOperation->getValue()) . ')';
            case IsEmpty::class:
                /** @var IsEmpty $abstractOperation */
                return 'ISNULL(' . $abstractOperation->getKey() .')';
            case NotEmpty::class:
                /** @var NotEmpty $abstractOperation */
                return $abstractOperation->getKey() . ' IS NOT NULL';
            case NotEqual::class:
                /** @var NotEqual $abstractOperation */
                return $abstractOperation->getKey() . '<>' . $this->convertValue($abstractOperation->getValue());
            case Between::class:
                /** @var Between $abstractOperation */
                return $abstractOperation->getKey() . ' BETWEEN '
                    . $this->convertValue($abstractOperation->getMin())
                    . ' AND ' . $this->convertValue($abstractOperation->getMax());
        }

        return '';
    }

    protected function convertSoring(Sorting $sorting): string
    {
        $parts = [];
        foreach ($sorting->getParts() as $part) {
            $parts[] = $part[0] . ' ' . $this->getSqlAscDesc($part[1]);
        }
        return 'ORDER BY ' . implode(',', $parts);
    }

    protected function getSqlAscDesc($sort): string
    {
        switch ($sort) {
            case Sorting::ASC:
                return 'ASC';
            case Sorting::DESC:
                return 'DESC';
            default:
                throw new \InvalidArgumentException('Sorting can be only ASC or DESC');
        }
    }

    protected function convertValue($value): string
    {
        if (is_array($value)) {
            $arrayOfValues = [];
            foreach ($value as $v) {
                $arrayOfValues[] = $this->convertValue($v);
            }
            return implode(', ', $arrayOfValues);
        }

        if (is_string($value)) {
            return "'" . $value . "'";
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $value;
    }
}
