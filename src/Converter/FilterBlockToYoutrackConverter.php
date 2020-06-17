<?php


namespace AFQ\Converter;

use AFQ\Block\AndFilterBlock;
use AFQ\Block\AbstractFilterBlock;
use AFQ\Block\OrFilterBlock;
use AFQ\Comparison\AbstractOperation;
use AFQ\Comparison\Between;
use AFQ\Comparison\CloseDateBetween;
use AFQ\Comparison\Equal;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\IsOpen;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Comparison\WithOutTag;
use AFQ\Comparison\WithTag;
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
                return $abstractOperation->getKey() . ': ' . $this->convertValue($abstractOperation->getValue());

            case In::class:
                /** @var In $abstractOperation */
                return $abstractOperation->getKey() . ': '
                    . implode(',', $this->convertValue($abstractOperation->getValue()));

            case NotIn::class:
                /** @var NotIn $abstractOperation */
                return $abstractOperation->getKey() . ': -'
                    . implode(',-', $this->convertValue($abstractOperation->getValue()));

            case IsEmpty::class:
                /** @var IsEmpty $abstractOperation */
                return $abstractOperation->getKey() . ': {Нет: ' . mb_strtolower($abstractOperation->getKey()) . '}';

            case NotEmpty::class:
                /** @var NotEmpty $abstractOperation */
                return 'имеет: ' . $abstractOperation->getKey();

            case NotEqual::class:
                /** @var NotEqual $abstractOperation */
                return $abstractOperation->getKey() . ': -' . $this->convertValue($abstractOperation->getValue());

            case Between::class:
                /** @var Between $abstractOperation */
                return $abstractOperation->getKey() . ': '
                    . $abstractOperation->getMin()
                    . ' .. '
                    . $abstractOperation->getMax();

            case CloseDateBetween::class:
                /** @var CloseDateBetween $abstractOperation */
                $dateString = date('Y-m-d_H:i', $abstractOperation->getFrom());
                if ($abstractOperation->getTo()) {
                    $dateString .= ' .. ' . date('Y-m-d_H:i', $abstractOperation->getTo());
                }
                return 'дата завершения: ' . $dateString;

            case IsOpen::class:
                /** @var IsOpen $abstractOperation */
                return $abstractOperation->isOpen() ? '#Незавершенная' : '#Завершенная';

            case WithTag::class:
                /** @var WithTag $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getTags() as $tag) {
                    $parts[] = "{$tag}";
                }
                return 'тег: ' . implode(',', $parts);

            case WithOutTag::class:
                /** @var WithTag $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getTags() as $tag) {
                    $parts[] = "{$tag}";
                }
                return 'тег: -' . implode(',-', $parts);
        }

        return '';
    }

    protected function convertSoring(Sorting $sorting): string
    {
        $parts = [];
        foreach ($sorting->getParts() as $part) {
            $parts[] = $part[0] . ' ' . $this->geAscDescString($part[1]);
        }
        return 'Сортировать: ' . implode(',', $parts);
    }

    protected function geAscDescString($sort): string
    {
        switch ($sort) {
            case Sorting::ASC:
                return 'по возр.';
            case Sorting::DESC:
                return 'по убыв.';
        }

        return '';
    }

    /**
     * @param mixed $value
     *
     * @return string|array
     */
    protected function convertValue($value)
    {
        if (is_array($value)) {
            $values = [];
            foreach ($value as $v) {
                $values[] = $this->convertValue($v);
            }

            return $values;
        }
        if (strpos($value, ' ') !== false) {
            return '{' . $value . '}';
        }

        return $value;
    }
}
