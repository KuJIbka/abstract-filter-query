<?php

namespace AFQ\Comparison;

use DateTimeInterface;

abstract class AbstractDateBetween extends AbstractOperation
{
    public function __construct(
        protected DateTimeInterface $from,
        protected DateTimeInterface $to
    ) {
    }

    public function getFrom(): DateTimeInterface
    {
        return $this->from;
    }

    public function getTo(): DateTimeInterface
    {
        return $this->to;
    }
}