<?php

namespace AFQ\Block;

use AFQ\Comparison\AbstractOperation;

abstract class AbstractFilterBlock
{
    protected $subBlocks = [];

    public function __construct($args = [])
    {
        $this->addMultiple($args);
    }

    public function addMultiple($args = []): self
    {
        foreach ((array) $args as $arg) {
            $this->add($arg);
        }

        return $this;
    }

    public function add($arg): self
    {
        if ($arg !== null) {
            if (!$arg instanceof AbstractOperation && !$arg instanceof AbstractFilterBlock) {
                throw new \InvalidArgumentException('$arg must extends AbstractOperation or AbstractFilterBlock class');
            }
            $this->subBlocks[] = $arg;
        }

        return $this;
    }

    public function getSubBlocks(): array
    {
        return $this->subBlocks;
    }
}
