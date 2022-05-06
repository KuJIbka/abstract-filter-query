<?php

namespace AFQ\Converter;

use AFQ\Block\AbstractFilterBlock;
use AFQ\Block\AndFilterBlock;
use AFQ\Block\OrFilterBlock;
use AFQ\Comparison\AbstractOperation;
use AFQ\Comparison\Between;
use AFQ\Comparison\CloseDateBetween;
use AFQ\Comparison\CreateDateBetween;
use AFQ\Comparison\Equal;
use AFQ\Comparison\IdIn;
use AFQ\Comparison\IdNotIn;
use AFQ\Comparison\In;
use AFQ\Comparison\IsEmpty;
use AFQ\Comparison\IsOpen;
use AFQ\Comparison\NotEmpty;
use AFQ\Comparison\NotEqual;
use AFQ\Comparison\NotIn;
use AFQ\Comparison\ProjectIn;
use AFQ\Comparison\RawString;
use AFQ\Comparison\UpdateDateBetween;
use AFQ\Comparison\WithOutTag;
use AFQ\Comparison\WithTag;
use AFQ\Comparison\WorkItemAuthors;
use AFQ\Sorting\Sorting;
use DateTimeInterface;

class FilterBlockToJiraConverter extends AbstractConverter
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
            $result = '(' . implode(' and ', $partsAsString) . ')';
        } elseif ($abstractFilterBlock instanceof OrFilterBlock) {
            $result = '(' . implode(' or ', $partsAsString) . ')';
        }

        return $result;
    }

    protected function convertOperation(AbstractOperation $abstractOperation): string
    {
        $className = get_class($abstractOperation);
        switch ($className) {
            case Equal::class:
                /** @var Equal $abstractOperation */
                return '"'.$abstractOperation->getKey() . '" = ' . $this->convertValue($abstractOperation->getValue());

            case In::class:
                /** @var In $abstractOperation */
                return '"'.$abstractOperation->getKey() . '" in ('
                    . implode(',', $this->convertValue($abstractOperation->getValue())) . ')';

            case NotIn::class:
                /** @var NotIn $abstractOperation */
                return '"'.$abstractOperation->getKey() . '" not in ('
                    . implode(',', $this->convertValue($abstractOperation->getValue())) . ')';

            case IsEmpty::class:
                /** @var IsEmpty $abstractOperation */
                return '"'.$abstractOperation->getKey() . '" is empty';

            case NotEmpty::class:
                /** @var NotEmpty $abstractOperation */
                return '"'. $abstractOperation->getKey() . '" is not empty';

            case NotEqual::class:
                /** @var NotEqual $abstractOperation */
                return '"'. $abstractOperation->getKey() . '" != ' . $this->convertValue($abstractOperation->getValue());

            case Between::class:
                /** @var Between $abstractOperation */
                return '("'.$abstractOperation->getKey() . '" >= '
                    . $this->convertValue($abstractOperation->getMin())
                    . ' and "' . $abstractOperation->getKey() . '" <= '
                    . $this->convertValue($abstractOperation->getMax()).')';

            case CreateDateBetween::class:
                /** @var CreateDateBetween $abstractOperation */
                return '(created >= ' . $this->convertValue($abstractOperation->getFrom())
                    . ' and created <= ' . $this->convertValue($abstractOperation->getTo()).')';
            case CloseDateBetween::class:
                /** @var CloseDateBetween $abstractOperation */
                return '(resolved >= ' . $this->convertValue($abstractOperation->getFrom())
                    . ' and resolved <= ' . $this->convertValue($abstractOperation->getTo()).')';

            case UpdateDateBetween::class:
                /** @var UpdateDateBetween $abstractOperation */
                return '(updated >= ' . $this->convertValue($abstractOperation->getFrom())
                    . ' and updated <= ' . $this->convertValue($abstractOperation->getTo()).')';

            case IsOpen::class:
                /** @var IsOpen $abstractOperation */
                return $abstractOperation->isOpen() ? 'resolved is empty' : 'resolved is not empty';

            case WithTag::class:
                /** @var WithTag $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getTags() as $tag) {
                    $parts[] = (string) ($this->convertValue($tag));
                }

                return 'labels in (' . implode(',', $parts) . ')';

            case WithOutTag::class:
                /** @var WithTag $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getTags() as $tag) {
                    $parts[] = (string) ($this->convertValue($tag));
                }

                return 'labels not in (' . implode(',', $parts) . ')';

            case IdIn::class:
                /** @var IdIn $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getValue() as $value) {
                    $parts[] = (string) ($this->convertValue($value));
                }

                return 'issueKey in (' . implode(',', $parts) . ')';

            case IdNotIn::class:
                /** @var IdNotIn $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getValue() as $value) {
                    $parts[] = (string) ($this->convertValue($value));
                }

                return 'issueKey not in (' . implode(',', $parts) . ')';
            case ProjectIn::class:
                /** @var ProjectIn $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getValue() as $value) {
                    $parts[] = '"' . $this->convertValue($value) . '"';
                }

                return 'project in (' . implode(',', $parts) . ')';
            case RawString::class:
                /** @var RawString $abstractOperation */
                return $abstractOperation->getValue();

            case WorkItemAuthors::class:
                /** @var WorkItemAuthors $abstractOperation */
                $parts = [];
                foreach ($abstractOperation->getValue() as $value) {
                    $parts[] = '"' . $this->convertValue($value) . '"';
                }
                return 'worklogAuthor in (' . implode(',', $parts) . ')';
        }

        return '';
    }

    protected function convertSoring(Sorting $sorting): string
    {
        $parts = [];
        foreach ($sorting->getParts() as $part) {
            $parts[] = $part[0] . ' ' . $this->geAscDescString($part[1]);
        }

        return 'order by ' . implode(',', $parts);
    }

    protected function geAscDescString($sort): string
    {
        switch ($sort) {
            case Sorting::ASC:
                return 'asc';
            case Sorting::DESC:
                return 'desc';
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
        if ($value instanceof DateTimeInterface) {
            /** @var DateTimeInterface $value */
            return '"' . $value->format('Y-m-d H:i') . '"';
        }

        if (strpos($value, ' ') !== false) {
            return '"' . $value . '"';
        }

        return $value;
    }
}
