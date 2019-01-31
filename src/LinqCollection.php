<?php

namespace SubjectivePHP\Linq;

use ArrayIterator;
use CallbackFilterIterator;
use Countable;
use InvalidArgumentException;
use Traversable;
use IteratorAggregate;
use LimitIterator;

final class LinqCollection implements IteratorAggregate, Countable
{
    /**
     * @var Traversable
     */
    private $traversable;

    /**
     * LinqCollection constructor.
     *
     * @param Traversable $traversable The items to be linq'd.
     */
    private function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    /**
     * Create a new LinqCollection from the given input.
     *
     * @param array|Traversable $collection The items to be linq'd.
     *
     * @return LinqCollection
     *
     * @throws InvalidArgumentException Thrown if the given $collection is not an array or Traversable.
     */
    public static function from($collection) : LinqCollection
    {
        if ($collection instanceof Traversable) {
            return new self($collection);
        }

        if (is_array($collection)) {
            return new self(new ArrayIterator($collection));
        }

        throw new InvalidArgumentException(
            sprintf("Cannot create linq collection with type '%s'", gettype($collection))
        );
    }

    /**
     * Returns the first element in a sequence that satisfies a specified condition.
     *
     * @param callable|null $predicate A function to test each element for a condition.
     *
     * @return mixed
     *
     * @throws InvalidOperationException Thrown if no element satisfies the condition in $predicate or the sequence
     *                                   is empty.
     */
    public function first(callable $predicate = null)
    {
        $noMatchValue = new \StdClass();
        $result = $this->firstOrDefault($predicate, $noMatchValue);
        if ($result === $noMatchValue) {
            throw new InvalidOperationException('No elements in sequence match the condition.');
        }

        return $result;
    }

    /**
     * Returns the first element of a sequence, or a default value if no element is found.
     *
     * @param callable|null $predicate    A function to test each element for a condition.
     * @param mixed         $defaultValue The default value to return.
     *
     * @return mixed
     */
    public function firstOrDefault(callable $predicate = null, $defaultValue = null)
    {
        $predicate = $predicate ?? function ($item) : bool {
            return true;
        };
        foreach ($this->traversable as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }

        return $defaultValue;
    }

    /**
     * Filters a sequence of values based on a predicate.
     *
     * @param callable $where A function to test each source element for a condition
     *
     * @return LinqCollection
     */
    public function where(callable $where) : LinqCollection
    {
        return new self(new CallbackFilterIterator($this->traversable, $where));
    }

    /**
     * Bypasses a specified number of elements in a sequence and then returns the remaining elements
     *
     * @param int $offset The number of elements to skip before returning the remaining elements.
     *
     * @return LinqCollection
     */
    public function skip(int $offset) : LinqCollection
    {
        return new self(new LimitIterator($this->traversable, $offset));
    }

    /**
     * Returns a specified number of contiguous elements from the start of a sequence.
     *
     * @param int $count The number of elements to return.
     *
     * @return LinqCollection
     */
    public function take(int $count) : LinqCollection
    {
        return new self(new LimitIterator($this->traversable, 0, $count));
    }

    /**
     * Counts the elements in the sequence.
     *
     * @return int
     */
    public function count() : int
    {
        return iterator_count($this->traversable);
    }

    /**
     * Projects each element of a sequence into a new form.
     *
     * @param callable $selector A transform function to apply to each element.
     *
     * @return LinqCollection
     */
    public function select(callable $selector) : LinqCollection
    {
        return new self(new SelectIterator($this->traversable, $selector));
    }

    /**
     * Sorts the elements of a sequence in order by using a specified comparer.
     *
     * @param callable $comparer A function used to compare each element in the sequence.
     *
     * @return LinqCollection
     */
    public function orderBy(callable $comparer) : LinqCollection
    {
        $iterator = new ArrayIterator(iterator_to_array($this->traversable));
        $iterator->uasort($comparer);
        return new self($iterator);
    }

    /**
     * Return an external iterator.
     *
     * @return Traversable
     */
    public function getIterator() : Traversable
    {
        return $this->traversable;
    }
}
