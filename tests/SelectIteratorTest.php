<?php

namespace SubjectivePHPTest\Linq;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SubjectivePHP\Linq\SelectIterator;

/**
 * @coversDefaultClass \SubjectivePHP\Linq\SelectIterator
 * @covers ::__construct
 */
final class SelectIteratorTest extends TestCase
{
    /**
     * @test
     * @covers ::getIterator
     */
    public function getIteratorYeildsResults()
    {
        $iterator = new ArrayIterator(
           [
               ['id' => 1, 'price' => 1.0],
               ['id' => 2, 'price' => 2.0],
               ['id' => 2, 'price' => 2.0],
           ]
        );

        $selector = function (array $item) : array {
            return ['id' => $item['id']];
        };

        $selectIterator = new SelectIterator($iterator, $selector);

        $this->assertSame(
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 2],
            ],
            iterator_to_array($selectIterator)
        );
    }
}
