<?php


namespace AFQ\Comparison;

use DateTimeImmutable;

class CloseDateBetween extends AbstractOperation
{
    protected $from;
    protected $to;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom(): DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): ?DateTimeImmutable
    {
        return $this->to;
    }
}
