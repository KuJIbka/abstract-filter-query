<?php


namespace AFQ\Comparison;

class IsOpen extends AbstractOperation
{
    protected $isOpen = false;

    public function __construct(bool $isOpen = false)
    {
        $this->isOpen = $isOpen;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->isOpen;
    }
}
