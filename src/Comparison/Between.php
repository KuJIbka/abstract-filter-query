<?php


namespace AFQ\Comparison;

class Between extends AbstractOperation
{
    protected $key;
    protected $min;
    protected $max;

    public function __construct($key, $min, $max)
    {
        $this->key = $key;
        $this->min = $min;
        $this->max = $max;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function getMax()
    {
        return $this->max;
    }
}
