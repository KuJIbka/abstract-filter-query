<?php


namespace AFQ\Comparison;

class WithOutTag extends AbstractOperation
{
    protected $tags = [];

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
