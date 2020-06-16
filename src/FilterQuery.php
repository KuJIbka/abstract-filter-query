<?php

namespace AFQ;

use AFQ\Block\AbstractFilterBlock;
use AFQ\Sorting\Sorting;

class FilterQuery
{
    protected $filterBlock;
    protected $sorting;

    public function setFilterBlock(AbstractFilterBlock $abstractFilterBlock)
    {
        $this->filterBlock = $abstractFilterBlock;

        return $this;
    }

    public function getFilterBlock()
    {
        return $this->filterBlock;
    }

    public function setSorting(Sorting $abstractSorting)
    {
        $this->sorting = $abstractSorting;

        return $this;
    }

    public function getSorting()
    {
        return $this->sorting;
    }
}
