<?php


namespace AFQ\Sorting;

class Sorting
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    protected $parts = [];

    public function __construct(array $parts = [])
    {
        $this->addMultiple($parts);
    }

    public function addMultiple(array $parts = []): self
    {
        foreach ($parts as $part) {
            $this->add($part[0], $part[1]);
        }

        return $this;
    }

    public function add($key, $sort): self
    {
        if (!in_array($sort, [self::ASC, self::DESC])) {
            throw new \InvalidArgumentException('sorting can be only ASC or DESC');
        }
        $this->parts[] = [$key, $sort];

        return $this;
    }

    public function getParts(): array
    {
        return $this->parts;
    }
}
