<?php

namespace AFQ\Comparison;

class ProjectIn extends AbstractOperation
{
    public function __construct(
        protected array $value
    ) {
    }

    public function getValue(): array
    {
        return $this->value;
    }
}
