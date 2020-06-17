<?php


namespace AFQ\Converter;

use AFQ\Block\AbstractFilterBlock;
use AFQ\Comparison\AbstractOperation;
use AFQ\FilterQuery;
use AFQ\Sorting\Sorting;

abstract class AbstractConverter
{
    abstract protected function convertBlock(AbstractFilterBlock $abstractFilterBlock): string;
    abstract protected function convertOperation(AbstractOperation $abstractOperation): string;
    abstract protected function convertSoring(Sorting $sorting): string;

    public function convertFilterQuery(FilterQuery $filterQuery): string
    {
        $result = '';
        if ($filterQuery->getFilterBlock()) {
            $result = $this->convertBlock($filterQuery->getFilterBlock()) . ' ';
        }
        if ($filterQuery->getSorting()) {
            $result .= $this->convertSoring($filterQuery->getSorting());
        }

        return trim($result);
    }
}
