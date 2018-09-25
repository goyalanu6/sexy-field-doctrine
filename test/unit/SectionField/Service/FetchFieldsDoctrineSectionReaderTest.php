<?php
declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Mockery as m;
use Doctrine\ORM;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Tardigrades\SectionField\Service\FetchFieldsDoctrineSectionReader
 * @covers ::__construct
 * @covers ::buildQuery
 * @covers ::<private>
 */
final class FetchFieldsDoctrineSectionReaderTest extends TestCase
{
    /** @var ORM\Entitymanager|m\Mock */
    private $entityManager;

    /** @var FetchFieldsDoctrineSectionReader */
    private $reader;

    protected function setUp()
    {
        $this->entityManager = m::mock(ORM\EntityManager::class)->makePartial();
        $this->reader = new FetchFieldsDoctrineSectionReader($this->entityManager);
    }

    public function testOneToManyJoins(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => \TestNS\Product::class,
            ReadOptions::FETCH_FIELDS => 'slug,prices,price,currency,product',
            ReadOptions::LIMIT => 5,
            ReadOptions::ORDER_BY => ['product:prices:price' => 'ASC']
        ]);
        $expected = static::normalize(<<<'DQL'
            SELECT
              product.productSlug AS product:productSlug,
              product:prices.priceSlug AS product:prices:priceSlug,
              product:prices.price AS product:prices:price,
              product:prices.currency AS product:prices:currency
            FROM TestNS\Product product
            LEFT JOIN TestNS\Price product:prices WITH product = product:prices.product
            ORDER BY product:prices:price ASC
DQL
        );

        $builder = $this->reader->buildQuery($readOptions);
        $this->assertSame($expected, $builder->getDQL());
        $this->assertSame(5, $builder->getMaxResults());
    }

    public function testManyToOneJoins(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => \TestNS\Price::class,
            ReadOptions::FETCH_FIELDS => 'slug,product,name'
        ]);
        $expected = static::normalize(<<<'DQL'
            SELECT
              price.priceSlug AS price:priceSlug,
              price:product.productSlug AS price:product:productSlug,
              price:product.name AS price:product:name
            FROM TestNS\Price price
            LEFT JOIN TestNS\Product price:product WITH price:product = price.product
DQL
        );

        $builder = $this->reader->buildQuery($readOptions);
        $this->assertSame($expected, $builder->getDQL());
    }

    public function testMultipleFieldValues(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => \TestNS\Product::class,
            ReadOptions::FIELD => ['name' => ['Space Frikandel', 'Ongewokkel']],
            ReadOptions::FETCH_FIELDS => 'name,slug'
        ]);
        $expected = static::normalize(<<< DQL
            SELECT
              product.name AS product:name,
              product.productSlug AS product:productSlug
            FROM TestNS\Product product
            WHERE product.name IN (?1)
DQL
        );

        $builder = $this->reader->buildQuery($readOptions);
        $this->assertSame($expected, $builder->getDQL());
        $this->assertSame(['Space Frikandel', 'Ongewokkel'], $builder->getParameter(1)->getValue());
    }

    public function testSlugFetch(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => \TestNS\Product::class,
            ReadOptions::SLUG => 'ikbeneenproduct',
            ReadOptions::FETCH_FIELDS => 'name'
        ]);
        $expected = static::normalize(<<< DQL
            SELECT
              product.name AS product:name
            FROM TestNS\Product product
            WHERE product.productSlug = (?1)
DQL
        );

        $builder = $this->reader->buildQuery($readOptions);
        $this->assertSame($expected, $builder->getDQL());
        $this->assertSame('ikbeneenproduct', $builder->getParameter(1)->getValue());
    }

    /**
     * @expectedException \Tardigrades\SectionField\Service\InvalidFetchFieldsQueryException
     * @expectedExceptionMessage Class doesn't have a slug field
     */
    public function testThatItFailsOnASluglessClass(): void
    {
        $this->reader->buildQuery(
            ReadOptions::fromArray([
                ReadOptions::SECTION => \TestNS\Slugless::class,
                ReadOptions::FETCH_FIELDS => 'slug'
            ])
        );
    }

    /**
     * @expectedException \Tardigrades\SectionField\Service\InvalidFetchFieldsQueryException
     * @expectedExceptionMessage Not selecting any fields
     */
    public function testThatItFailsWhenNotSelectingFields(): void
    {
        $this->reader->buildQuery(
            ReadOptions::fromArray([
                ReadOptions::SECTION => \TestNS\Product::class,
                ReadOptions::FETCH_FIELDS => ''
            ])
        );
    }

    // phpcs:ignore Generic.Files.LineLength
    /**
     * @expectedException \Tardigrades\SectionField\Service\InvalidFetchFieldsQueryException
     * @expectedExceptionMessage inverse side of TestNS\MalformedRelationshipA::foo on TestNS\MalformedRelationshipB
     */
    public function testThatItFailsIfNeitherSideOwnsARelationship(): void
    {
        $this->reader->buildQuery(
            ReadOptions::fromArray([
                ReadOptions::SECTION => \TestNS\MalformedRelationshipA::class,
                ReadOptions::FETCH_FIELDS => 'foo'
            ])
        );
    }

    /** @covers ::makeNested */
    public function testNesting(): void
    {
        $this->assertSame(
            [
                'foo' => [
                    'bar' => 3,
                    'baz' => [
                        'qux' => 'foobar',
                        'bar' => 'foo'
                    ]
                ],
                'bar' => 10
            ],
            $this->reader::makeNested([
                'foo:bar' => 3,
                'foo:baz:qux' => 'foobar',
                'foo:baz:bar' => 'foo',
                'bar' => 10
            ])
        );
    }

    private static function normalize(string $query): string
    {
        return trim(preg_replace('/\\s\\s+/', ' ', $query));
    }
}

namespace TestNS;

class Product
{
    public static function fieldInfo(): array
    {
        return [
            'productSlug' => [
                'handle' => 'productSlug',
                'type' => 'Slug',
                'getter' => 'getProductSlug',
                'setter' => 'setProductSlug',
                'relationship' => null
            ],
            'name' => [
                'handle' => 'name',
                'type' => 'TextInput',
                'getter' => 'getName',
                'setter' => 'setName',
                'relationship' => null
            ],
            'prices' => [
                'handle' => 'oneProductToManyPrice',
                'type' => 'Relationship',
                'getter' => 'getPrices',
                'setter' => 'addPrice',
                'relationship' => [
                    'class' => 'TestNS\\Price',
                    'plural' => true,
                    'kind' => 'one-to-many',
                    'owner' => false
                ]
            ],
            'categories' => [
                'handle' => 'manyProductToManyCategory',
                'type' => 'Relationship',
                'getter' => 'getProductCategories',
                'setter' => 'addProductCategory',
                'relationship' => [
                    'class' => 'TestNS\\ProductCategory',
                    'plural' => true,
                    'kind' => 'many-to-many',
                    'owner' => true
                ],
            ],
            'status' => [
                'handle' => 'manyProductToOneStatus',
                'type' => 'Relationship',
                'getter' => 'getStatus',
                'setter' => 'setStatus',
                'relationship' => [
                    'class' => 'TestNS\\Status',
                    'plural' => false,
                    'kind' => 'many-to-one',
                    'owner' => true
                ],
            ]
        ];
    }
}

class Price
{
    public static function fieldInfo(): array
    {
        return [
            'priceSlug' => [
                'handle' => 'priceSlug',
                'type' => 'Slug',
                'getter' => 'getPriceSlug',
                'setter' => 'setPriceSlug',
                'relationship' => null,
            ],
            'price' => [
                'handle' => 'price',
                'type' => 'TextInput',
                'getter' => 'getPrice',
                'setter' => 'setPrice',
                'relationship' => null
            ],
            'currency' => [
                'handle' => 'currency',
                'type' => 'TextInput',
                'getter' => 'getCurrency',
                'setter' => 'setCurrency',
                'relationship' => null
            ],
            'product' => [
                'handle' => 'manyPriceToOneProduct',
                'type' => 'Relationship',
                'getter' => 'getProduct',
                'setter' => 'setProduct',
                'relationship' => [
                    'class' => 'TestNS\\Product',
                    'plural' => false,
                    'kind' => 'many-to-one',
                    'owner' => true
                ]
            ]
        ];
    }
}

class ProductCategory
{
    public static function fieldInfo(): array
    {
        return [
            'productCategorySlug' => [
                'handle' => 'productCategorySlug',
                'type' => 'Slug',
                'getter' => 'getProductCategorySlug',
                'setter' => 'setProductCategorySlug',
                'relationship' => null
            ],
            'name' => [
                'handle' => 'name',
                'type' => 'TextInput',
                'getter' => 'getName',
                'setter' => 'setName',
                'relationship' => null
            ],
        ];
    }
}

class Status
{
    public static function fieldInfo(): array
    {
        return [
            'statusSlug' => [
                'handle' => 'statusSlug',
                'type' => 'Slug',
                'getter' => 'getStatusSlug',
                'setter' => 'setStatusSlug',
                'relationship' => null
            ],
            'value' => [
                'handle' => 'value',
                'type' => 'TextInput',
                'getter' => 'getValue',
                'setter' => 'setValue',
                'relationship' => null
            ]
        ];
    }
}

class Slugless
{
    public static function fieldInfo(): array
    {
        return [];
    }
}

class MalformedRelationshipA
{
    public static function fieldInfo(): array
    {
        return [
            'foo' => [
                'handle' => 'foo',
                'type' => 'Relationship',
                'relationship' => [
                    'class' => 'TestNS\\MalformedRelationshipB',
                    'plural' => false,
                    'kind' => 'one-to-one',
                    'owner' => false
                ]
            ]
        ];
    }
}

class MalformedRelationshipB
{
    public static function fieldInfo(): array
    {
        return [];
    }
}
