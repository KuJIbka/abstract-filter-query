<?php


namespace AFQ\Comparison;

class AbstractKeyOperation extends AbstractOperation
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}
