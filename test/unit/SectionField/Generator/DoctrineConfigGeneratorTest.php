<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\Generator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tardigrades\Entity\Field;
use Tardigrades\Entity\FieldType;
use Tardigrades\Entity\Section;
use Tardigrades\SectionField\Generator\Writer\Writable;
use Tardigrades\SectionField\Service\FieldManagerInterface;
use Tardigrades\SectionField\Service\FieldTypeManagerInterface;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\FieldTypeGeneratorConfig;
use Tardigrades\SectionField\ValueObject\Handle;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Version;

/**
 * @coversDefaultClass Tardigrades\SectionField\Generator\DoctrineConfigGenerator
 * @covers ::<private>
 * @covers ::__construct
 */
final class DoctrineConfigGeneratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var FieldManagerInterface|Mockery\Mock */
    private $fieldManager;

    /** @var FieldTypeManagerInterface|Mockery\Mock */
    private $fieldTypeManager;

    /** @var SectionManagerInterface|Mockery\Mock */
    private $sectionManager;

    /** @var ContainerInterface|Mockery\Mock */
    private $containerManager;

    public function setUp()
    {
        $this->fieldManager = Mockery::mock(FieldManagerInterface::class);
        $this->fieldTypeManager = Mockery::mock(FieldTypeManagerInterface::class);
        $this->sectionManager = Mockery::mock(SectionManagerInterface::class);
        $this->containerManager = Mockery::mock(ContainerInterface::class);
    }

    /**
     * @test
     * @covers ::generateBySection
     */
    public function it_should_generate_by_a_section()
    {
        $sectionFields = [
            'section' => [
                'name' => 'I have a sexy name',
                'handle' => 'handle',
                'fields' => ['these', 'are', 'fields'],
                'slug' => ['these'],
                'default' => 'these',
                'namespace' => 'My\Namespace'
            ]
        ];

        $section = Mockery::mock(new Section())->makePartial();
        $section->shouldReceive('getConfig')->andReturn(SectionConfig::fromArray($sectionFields));
        $section->shouldReceive('getHandle')->andReturn(Handle::fromString('handle'));
        $section->shouldReceive('getVersion')->andReturn(Version::fromInt(5));

        $fieldHandle = [
            'handle' => [
                'name' => 'name',
                'handle' => 'wheee',
                'kind' => 'kid',
                'to' => 'to'
            ]
        ];
        $one = new Field();
        $one->setFieldType(new FieldType());
        $one->getFieldType()->setFullyQualifiedClassName('fullname');
        $one->setName('name');
        $one->getFieldType()->setType('atypical');
        $fieldtypeConfigDoctrine = FieldTypeGeneratorConfig::fromArray(
            [
                'doctrine' => [
                    'a' => new \stdClass(),
                    'c' => new \stdClass()
                ]
            ]
        );

        $containerInter = Mockery::mock(ContainerInterface::class)->makePartial();
        $containerInter->shouldReceive('getFieldTypeGeneratorConfig')
            ->andReturn($fieldtypeConfigDoctrine);

        $this->fieldManager->shouldReceive('readByHandles')
            ->once()
            ->andReturn([$one]);

        $this->sectionManager->shouldReceive('getRelationshipsOfAll')
            ->once()
            ->andReturn($fieldHandle);

        $this->containerManager->shouldReceive('get')
            ->once()
            ->andReturn($containerInter);

        $generator = new DoctrineConfigGenerator(
            $this->fieldManager,
            $this->fieldTypeManager,
            $this->sectionManager,
            $this->containerManager
        );

        $writable = $generator->generateBySection($section);
        $this->assertInstanceOf(Writable::class, $writable);
    }
}
