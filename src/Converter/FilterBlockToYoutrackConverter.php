<?php


namespace AFQ\Converter;

use AFQ\Block\AndFilterBlock;
use AFQ\Block\AbstractFilterBlock;
use AFQ\Block\OrFilterBlock;
use AFQ\Comparison\AbstractOperation;
use AFQ\Comparison\Between;
use AFQ\Comparison\Equal;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Sorting\Sorting;

class FilterBlockToYoutrackConverter extends AbstractConverter
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
            $result = '(' . implode(' и ', $partsAsString) . ')';
        } elseif ($abstractFilterBlock instanceof OrFilterBlock) {
            $result = '(' . implode(' или ', $partsAsString) . ')';
        }

        return $result;
    }

    protected function convertOperation(AbstractOperation $abstractOperation): string
    {
        $className = get_class($abstractOperation);
        switch ($className) {
            case Equal::class:
                /** @var Equal $abstractOperation */
                return $abstractOperation->getKey() . ': ' . $abstractOperation->getValue();
            case In::class:
                /** @var In $abstractOperation */
                return $abstractOperation->getKey() . ': ' . implode(',', $abstractOperation->getValue());
            case NotIn::class:
                /** @var NotIn $abstractOperation */
                return $abstractOperation->getKey() . ': -' . implode(',-', $abstractOperation->getValue());
            case IsEmpty::class:
                /** @var IsEmpty $abstractOperation */
                return $abstractOperation->getKey() . ': {Нет: ' . mb_strtolower($abstractOperation->getKey()) . '}';
            case NotEmpty::class:
                /** @var NotEmpty $abstractOperation */
                return 'имеет: ' . $abstractOperation->getKey();
            case NotEqual::class:
                /** @var NotEqual $abstractOperation */
                return $abstractOperation->getKey() . ': -' . $abstractOperation->getValue();
            case Between::class:
                /** @var Between $abstractOperation */
                return $abstractOperation->getKey() . ': '
                    . $abstractOperation->getMin()
                    . ' .. '
                    . $abstractOperation->getMax();
        }

        return '';
    }

    protected function convertSoring(Sorting $sorting): string
    {
        $parts = [];
        foreach ($sorting->getParts() as $part) {
            $parts[] = $part[0] . ' ' . $this->getSqlAscDesc($part[1]);
        }
        return 'Сортировать: ' . implode(',', $parts);
    }

    protected function getSqlAscDesc($sort): string
    {
        switch ($sort) {
            case Sorting::ASC:
                return 'по возр.';
            case Sorting::DESC:
                return 'по убыв.';
        }

        return '';
    }
}
