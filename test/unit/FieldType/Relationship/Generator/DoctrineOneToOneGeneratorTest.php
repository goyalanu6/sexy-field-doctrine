<?php
declare (strict_types=1);

namespace Tardigrades\Fieldtype\Relationship\Generator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tardigrades\Entity\Field;
use Tardigrades\Entity\SectionInterface;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\Generator\Loader\TemplateNotFoundException;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\FieldConfig;
use Tardigrades\SectionField\ValueObject\Handle;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Version;

/**
 * @coversDefaultClass Tardigrades\FieldType\Relationship\Generator\DoctrineOneToOneGenerator
 * @covers ::<private>
 */
final class DoctrineOneToOneGeneratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @covers ::generate
     */
    public function it_should_generate_with_wrong_kind()
    {
        $field = new Field();

        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'niets',
                    'kind' => 'wrong kind'
                ]
        ];
        $field = $field->setConfig($fieldArrayThing);

        $options = [
            'sectionManager' => [
                'handle' => 'what'
            ],
            'sectionConfig' => [
                'field' => [
                    'name' => 'iets',
                    'handle' => 'niets'
                ]
            ]
        ];

        $generated = DoctrineOneToOneGenerator::generate(
            $field,
            TemplateDir::fromString(''),
            $options
        );

        $this->assertEquals(Template::create(''), $generated);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function it_should_generate_with_correct_kind()
    {
        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'some handle',
                    'kind' => DoctrineOneToOneGenerator::KIND,
                    'from' => 'me',
                    'to' => 'you',
                    'type' => 'not my type'
                ]
        ];
        $fieldConfig = FieldConfig::fromArray($fieldArrayThing);

        $field = Mockery::mock(new Field())
            ->shouldDeferMissing()
            ->shouldReceive('getConfig')
            ->andReturn($fieldConfig)
            ->getMock();

        $doctrineSectionManager = Mockery::mock(SectionManagerInterface::class);

        $fromSectionInterface = Mockery::mock(SectionInterface::class);
        $toSectionInterface = Mockery::mock(SectionInterface::class);

        $doctrineSectionManager->shouldReceive('readByHandle')
            ->once()
            ->andReturn($fromSectionInterface);

        $doctrineSectionManager->shouldReceive('readByHandle')
            ->once()
            ->andReturn($toSectionInterface);

        $fromSectionInterface->shouldReceive('getVersion')
            ->twice()
            ->andReturn(Version::fromInt(37));

        $toSectionInterface->shouldReceive('getVersion')
            ->twice()
            ->andReturn(Version::fromInt(333));

        $toSectionConfig =
        SectionConfig::fromArray(
            [
                'section' => [
                    'name' => 'nameTo',
                    'handle' => 'handle',
                    'fields' => ['a' => 'b'],
                    'default' => 'default',
                    'namespace' => 'namespace'
                ]
            ]
        );

        $toSectionInterface->shouldReceive('getConfig')
            ->once()
            ->andReturn($toSectionConfig);

        $options = [
            'sectionManager' => $doctrineSectionManager,
            'sectionConfig' => SectionConfig::fromArray([
                'section' => [
                    'name' => 'iets',
                    'handle' => 'niets',
                    'fields' => ['a' => 'v'],
                    'default' => 'def',
                    'namespace' => 'nameInSpace'
                ]
            ])
        ];

        $generated = DoctrineOneToOneGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );

        $this->assertInstanceOf(Template::class, $generated);
    }
}
