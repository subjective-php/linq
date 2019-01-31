<?php

namespace SubjectivePHPTest\Linq;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use StdClass;
use SubjectivePHP\Linq\LinqCollection;

/**
 * @coversDefaultClass \SubjectivePHP\Linq\LinqCollection
 * @covers ::__construct
 * @covers ::getIterator
 * @covers ::<private>
 */
final class LinqCollectionTest extends TestCase
{
    /**
     * @var LinqCollection
     */
    private $collection;

    /**
     * Prepare each test.
     */
    public function setUp()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/books.json'));
        $this->collection = LinqCollection::from(new ArrayIterator($data));
    }

    /**
     * @test
     * @covers ::from
     */
    public function collectionCanBeCreatedFromIterator()
    {
        $array = json_decode(file_get_contents(__DIR__ . '/books.json'), true);
        $collection = LinqCollection::from(new ArrayIterator($array));
        $this->assertSame($array, iterator_to_array($collection));
    }

    /**
     * @test
     * @covers ::from
     */
    public function collectionCanBeCreatedFromArray()
    {
        $array = json_decode(file_get_contents(__DIR__ . '/books.json'), true);
        $collection = LinqCollection::from($array);
        $this->assertSame($array, iterator_to_array($collection));
    }

    /**
     * @test
     * @covers ::from
     * @expectedException \InvalidArgumentException
     */
    public function collectionCannotBeConstructedWithString()
    {
        LinqCollection::from('abcd');
    }

    /**
     * @test
     * @covers ::first
     * @expectedException \SubjectivePHP\Linq\InvalidOperationException
     */
    public function cannotCallFirstOnEmptyCollection()
    {
        $collection = LinqCollection::from([]);
        $collection->first();
    }

    /**
     * @test
     * @covers ::first
     */
    public function firstReturnsFirstElement()
    {
        $first = $this->collection->first();
        $this->assertSame('58339e95d5200', $first->id);
    }

    /**
     * @test
     * @covers ::firstOrDefault
     */
    public function firstReturnsDefaultIfEmpty()
    {
        $default = new \StdClass();
        $callable = function ($element) {
            return false;
        };
        $this->assertSame($default, $this->collection->firstOrDefault($callable, $default));
    }

    /**
     * @test
     * @covers ::count
     */
    public function countReturnsCountOfElementsInSequence()
    {
        $this->assertSame(11, $this->collection->count());
    }

    /**
     * @test
     * @covers ::orderBy
     */
    public function orderByOrdersSequence()
    {
        $collection = LinqCollection::from(['z', 'g', 'a', 'n']);
        $callable = function ($a, $b) {
            return strcmp($a, $b);
        };

        $this->assertSame(
            [2 => 'a', 1 => 'g', 3 => 'n', 0 => 'z'],
            iterator_to_array($collection->orderBy($callable))
        );
    }

    /**
     * @test
     * @covers ::skip
     * @covers ::take
     */
    public function skipAndTake()
    {
        $result = $this->collection->skip(2)->take(1);

        $this->assertEquals(
            [
                2 => (object)[
                    "author" => "Corets, Eva",
                    "title" => "Maeve Ascendant",
                    "genre" => "Fantasy",
                    "price" => 5.95,
                    "published" => 974437200,
                    "description" => 'After the collapse of a nanotechnology society in England, the young survivors '
                    . 'lay the foundation for a new society.',
                    "id" => "58339e95d526f"
                ],
            ],
            iterator_to_array($result)
        );
    }

    /**
     * @test
     */
    public function chainSkipTakeAndCount()
    {
        $this->assertSame(1, $this->collection->skip(3)->take(1)->count());
    }

    /**
     * @test
     */
    public function largeChain()
    {
        $actual = $this->collection->where(
            function (StdClass $book) : bool {
                return $book->genre === 'Computer';
            }
        )->select(
            function (StdClass $book) : array {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'salePrice' => round($book->price * 0.90, 2),
                ];
            }
        )->orderBy(
            function (array $thisBook, array $thatBook) : int {
                if ($thisBook['salePrice'] === $thatBook['salePrice']) {
                    return 0;
                }

                return ($thisBook['salePrice'] > $thatBook['salePrice']) ? 1 : -1;
            }
        )->skip(2)->take(1)->first();

        $expected = [
            'id' => '58339e95d5200',
            'title' => "XML Developer's Guide",
             'salePrice' => 40.46,
        ];

        $this->assertSame($actual, $expected);
    }

    /**
     * @test
     * @covers ::where
     */
    public function whereFilters()
    {
        $callable = function (StdClass $book) : bool {
            return $book->genre === 'Romance';
        };

        $result = $this->collection->where($callable);

        $this->assertEquals(
            [
                5 => (object)[
                    "author" => "Randall, Cynthia",
                    "title" => "Lover Birds",
                    "genre" => "Romance",
                    "price" => 4.95,
                    "published" => 967867200,
                    "description" => 'When Carla meets Paul at an ornithology conference, tempers fly as feathers get'
                    . ' ruffled.',
                    "id" => "58339e95d530e"
                ],
                6 => (object)[
                    "author" => "Thurman, Paula",
                    "title" => "Splish Splash",
                    "genre" => "Romance",
                    "price" => 4.95,
                    "published" => 973141200,
                    "description" => "A deep sea diver finds true love twenty thousand leagues beneath the sea.",
                    "id" => "58339e95d5343"
                ]
            ],
            iterator_to_array($result)
        );
    }

    /**
     * @test
     * @covers ::select
     */
    public function select()
    {
        $callable = function (StdClass $book) : array {
            return [
                'id' => $book->id,
                'genre' => $book->genre,
            ];
        };

        $result = $this->collection->select($callable);
        $this->assertSame(
            [
                ['id' => '58339e95d5200', 'genre' => 'Computer'],
                ['id' => '58339e95d5239', 'genre' => 'Fantasy'],
                ['id' => '58339e95d526f', 'genre' => 'Fantasy'],
                ['id' => '58339e95d52a4', 'genre' => 'Fantasy'],
                ['id' => '58339e95d52d9', 'genre' => 'Fantasy'],
                ['id' => '58339e95d530e', 'genre' => 'Romance'],
                ['id' => '58339e95d5343', 'genre' => 'Romance'],
                ['id' => '58339e95d5378', 'genre' => 'Horror'],
                ['id' => '58339e95d53ae', 'genre' => 'Science Fiction'],
                ['id' => '58339e95d53e4', 'genre' => 'Computer'],
                ['id' => '58339e95d5419', 'genre' => 'Computer'],
            ],
            iterator_to_array($result)
        );
    }
}
