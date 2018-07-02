<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tardigrades\SectionField\ValueObject\SectionConfig;

/**
 * @coversDefaultClass Tardigrades\SectionField\Service\QuerySectionReader
 * @covers ::<private>
 */
final class QuerySectionReaderTest extends TestCase
{
    /** @var QuerySectionReader */
    private $querySectionReader;

    public function setUp(): void
    {
        /** @var EntityManager|Mockery\Mock $entityManager */
        $entityManager = Mockery::mock(EntityManager::class);
        $queryBuilder = new QueryBuilder($entityManager);
        $expressionBuilder = new Expr();
        $entityManager->shouldReceive('getExpressionBuilder')->once()->andReturn($expressionBuilder);

        $this->querySectionReader = new QuerySectionReader($entityManager, $queryBuilder);
    }

    /**
     * @test
     */
    public function it_should_add_from_to_query(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
        $this->assertEquals(
            'SELECT Product FROM This\Is\A\Product Product',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_where_slug_by_fetch_fields_to_query(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::FETCH_FIELDS => 'slug'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
        $this->assertEquals(
            'SELECT Product.productSlug FROM This\Is\A\Product Product',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_join_one_to_many_to_query(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::FETCH_FIELDS => 'slug,prices,price,currency'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
        $this->assertEquals(
            'SELECT Product.productSlug, Price.priceSlug, Price.price, Price.currency FROM This\Is\A\Product Product LEFT JOIN This\Is\A\Price Price',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_join_many_to_many_to_query(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::FETCH_FIELDS => 'slug,name,categories'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
        $this->assertEquals(
            'SELECT FROM This\Is\A\Product Product',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_where_id_to_main_section(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::ID => 10
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());

        $this->assertEquals(
            'SELECT Product FROM This\Is\A\Product Product WHERE Product.id = :id',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_where_slug_to_main_section(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::SLUG => 'product-slug'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());

        $this->assertEquals(
            'SELECT Product FROM This\Is\A\Product Product WHERE Product.slug = :slug',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_where_by_field_value(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::FIELD => [ 'name' => 'Space Frikandel' ]
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());

        $this->assertEquals(
            'SELECT Product FROM This\Is\A\Product Product WHERE Product.name = :field',
            $this->querySectionReader->getDQL()
        );
    }

    /**
     * @test
     */
    public function it_should_add_where_by_multiple_field_values(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'This\Is\A\Product',
            ReadOptions::FIELD => ['name' => ['Space Frikandel', 'Ongewokkel']]
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());

        $this->assertEquals(
            'SELECT Product FROM This\Is\A\Product Product WHERE Product.name IN(:fields)',
            $this->querySectionReader->getDQL()
        );
    }

    private function givenASectionConfig(): SectionConfig
    {
        return SectionConfig::fromArray([
            'section' => [
                'name' => 'Product',
                'handle' => 'product',
                'fields' => [
                    'title' => [],
                    'productSlug' => []
                ],
                'default' => 'title',
                'slug' => 'productSlug',
                'namespace' => 'This\Is\A\Product'
            ]
        ]);
    }
}

namespace This\Is\A;

/**
 * Class Product
 *
 * Why can't I implement the CommonSectionInterface?
 *
 * @package This\Is\A
 */
class Product {

    const FIELDS = [
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
                'class' => 'This\\Is\\A\\Price',
                'plural' => true,
                'kind' => 'one-to-many'
            ]
        ],
        'categories' => [
            'handle' => 'manyProductToManyCategory',
            'type' => 'Relationship',
            'getter' => 'getProductCategories',
            'setter' => 'addProductCategory',
            'relationship' => [
                'class' => 'This\\Is\\A\\ProductCategory',
                'plural' => true,
                'kind' => 'many-to-many'
            ],
        ],
        'status' => [
            'handle' => 'manyProductToOneStatus',
            'type' => 'Relationship',
            'getter' => 'getStatus',
            'setter' => 'setStatus',
            'relationship' => [
                'class' => 'This\\Is\\A\\Status',
                'plural' => false,
                'kind' => 'many-to-one',
            ],
        ]
    ];

    /** @var string */
    protected $productSlug;

    /** @var string */
    protected $name;

    protected $prices;

    protected $categories;
}

namespace This\Is\A;

/**
 * Class Price
 * @package This\Is\A
 */
class Price
{
    const FIELDS = [
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
        ]
    ];

    /** @var string */
    protected $priceSlug;

    /** @var string */
    protected $price;

    /** @var string */
    protected $currency;
}

namespace This\Is\A;

/**
 * Class ProductCategory
 *
 * This is many to many related to a product
 *
 * @package This\Is\A
 */
class ProductCategory
{
    const FIELDS = [
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

namespace This\Is\A;

class Status
{
    const FIELDS = [
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
