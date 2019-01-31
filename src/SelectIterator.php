<?php

namespace SubjectivePHP\Linq;

use Traversable;
use IteratorAggregate;

final class SelectIterator implements IteratorAggregate
{
    /**
     * @var Traversable
     */
    private $collection;

    /**
     * @var callable
     */
    private $selector;

    /**
     * Create a SelectIterator from another iterator.
     *
     * @param Traversable $collection The iterator to be filtered.
     * @param callable    $selector   The callback which should return values from each item in the iterator.
     */
    public function __construct(Traversable $collection, callable $selector)
    {
        $this->collection = $collection;
        $this->selector = $selector;
    }

    /**
     * Returns an external iterator.
     *
     * @return Traversable
     */
    public function getIterator() : Traversable
    {
        foreach ($this->collection as $item) {
            yield ($this->selector)($item);
        }
    }
}
