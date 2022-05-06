<?php

namespace AFQ;

use AFQ\Block\AbstractFilterBlock;
use AFQ\Sorting\Sorting;

class FilterQuery
{
    public function __construct(
        protected ?AbstractFilterBlock $filterBlock = null,
        protected ?Sorting $sorting = null
    ) {
    }

    public function setFilterBlock(AbstractFilterBlock $abstractFilterBlock)
    {
        $this->filterBlock = $abstractFilterBlock;

        return $this;
    }

    public function getFilterBlock(): ?AbstractFilterBlock
    {
        return $this->filterBlock;
    }

    public function setSorting(Sorting $abstractSorting)
    {
        $this->sorting = $abstractSorting;

        return $this;
    }

    public function getSorting(): ?Sorting
    {
        return $this->sorting;
    }
}
