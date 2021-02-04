<?php

namespace AFQ\Comparison;

class RawString extends AbstractValueOperation
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}