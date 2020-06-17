<?php


namespace AFQ\Comparison;

class CloseDateBetween extends AbstractOperation
{
    protected $from;
    protected $to;

    public function __construct(int $from, int $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @return int|null
     */
    public function getTo(): ?int
    {
        return $this->to;
    }
}
