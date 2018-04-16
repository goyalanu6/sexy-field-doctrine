<?php
declare (strict_types=1);

namespace Tardigrades\FieldType\Relationship\Generator;

use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tardigrades\Entity\Field;
use Tardigrades\Entity\SectionInterface;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\FieldConfig;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Version;

/**
 * @coversDefaultClass Tardigrades\FieldType\Relationship\Generator\DoctrineManyToManyGenerator
 * @covers ::<private>
 */
final class DoctrineManyToManyGeneratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @covers ::generate
     */
    public function it_generates_an_empty_template_with_wrong_kind()
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
        $generated = DoctrineManyToManyGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );
        $this->assertEquals(Template::create(''), $generated);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function it_generates_a_proper_template_for_bidirectional_and_owner()
    {
        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'some handle',
                    'kind' => DoctrineManyToManyGenerator::KIND,
                    'relationship-type' => 'bidirectional',
                    'owner' => true,
                    'from' => 'this',
                    'to' => 'that',
                    'type' => 'my type',
                    'cascade' => 'all'
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
            ->andReturn(Version::fromInt(123));

        $toSectionConfig =
            SectionConfig::fromArray(
                [
                    'section' => [
                        'name' => 'nameTo',
                        'handle' => 'ToBeMapped',
                        'fields' => ['a', 'b'],
                        'default' => 'default',
                        'namespace' => 'nameFromSpace'
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
                    'handle' => 'mapper',
                    'fields' => ['a', 'v', 'b'],
                    'default' => 'def',
                    'namespace' => 'nameInSpace'
                ]
            ])
        ];

        $generated = DoctrineManyToManyGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );

        $expected = <<<'EOT'
<many-to-many field="thats_123" target-entity="nameFromSpace\Entity\ToBeMapped" inversed-by="mappers_37">
    <cascade>
        <cascade-all />
    </cascade>
    <join-table name="mappers_37_thats_123">
        <join-columns>
            <join-column name="mapper_37_id" referenced-column-name="id" />
        </join-columns>
        <inverse-join-columns>
            <join-column name="that_123_id" referenced-column-name="id" />
        </inverse-join-columns>
    </join-table>
</many-to-many>

EOT;

        $this->assertNotEmpty($generated);
        $this->assertInstanceOf(Template::class, $generated);
        $this->assertSame($expected, (string) $generated);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function it_generates_a_proper_template_for_bidirectional_and_not_owner()
    {
        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'some handle',
                    'kind' => DoctrineManyToManyGenerator::KIND,
                    'relationship-type' => 'bidirectional',
                    'owner' => false,
                    'from' => 'this',
                    'to' => 'that',
                    'type' => 'my type'
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
            ->andReturn(Version::fromInt(123));

        $toSectionConfig =
            SectionConfig::fromArray(
                [
                    'section' => [
                        'name' => 'nameTo',
                        'handle' => 'ToBeMapped',
                        'fields' => ['a', 'b'],
                        'default' => 'default',
                        'namespace' => 'nameFromSpace'
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
                    'handle' => 'mapper',
                    'fields' => ['a', 'v', 'b'],
                    'default' => 'def',
                    'namespace' => 'nameInSpace'
                ]
            ])
        ];

        $generated = DoctrineManyToManyGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );

        $expected = <<<'EOT'
<many-to-many field="thats_123" target-entity="nameFromSpace\Entity\ToBeMapped" mapped-by="mappers_37">
</many-to-many>

EOT;

        $this->assertNotEmpty($generated);
        $this->assertInstanceOf(Template::class, $generated);
        $this->assertSame($expected, (string) $generated);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function it_generates_a_proper_template_for_unidirectional()
    {
        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'some handle',
                    'kind' => DoctrineManyToManyGenerator::KIND,
                    'relationship-type' => 'unidirectional',
                    'owner' => true,
                    'from' => 'this',
                    'to' => 'that',
                    'type' => 'my type',
                    'cascade' => 'persist'
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
            ->andReturn(Version::fromInt(123));

        $toSectionConfig =
            SectionConfig::fromArray(
                [
                    'section' => [
                        'name' => 'nameTo',
                        'handle' => 'ToBeMapped',
                        'fields' => ['a', 'b'],
                        'default' => 'default',
                        'namespace' => 'nameFromSpace'
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
                    'handle' => 'mapper',
                    'fields' => ['a', 'v', 'b'],
                    'default' => 'def',
                    'namespace' => 'nameInSpace'
                ]
            ])
        ];

        $generated = DoctrineManyToManyGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );

        $expected = <<<'EOT'
<many-to-many field="thats_123" target-entity="nameFromSpace\Entity\ToBeMapped">
    <cascade>
        <cascade-persist />
    </cascade>
    <join-table name="mappers_37_thats_123">
        <join-columns>
            <join-column name="mapper_37_id" referenced-column-name="id" />
        </join-columns>
        <inverse-join-columns>
            <join-column name="that_123_id" referenced-column-name="id" />
        </inverse-join-columns>
    </join-table>
</many-to-many>

EOT;

        $this->assertNotEmpty($generated);
        $this->assertInstanceOf(Template::class, $generated);
        $this->assertSame($expected, (string) $generated);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function it_can_handle_field_aliases()
    {
        $fieldArrayThing = [
            'field' =>
                [
                    'name' => 'iets',
                    'handle' => 'some handle',
                    'kind' => DoctrineManyToManyGenerator::KIND,
                    'relationship-type' => 'bidirectional',
                    'owner' => true,
                    'from' => 'this',
                    'to' => 'that',
                    'as' => 'alias',
                    'type' => 'my type'
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
            ->andReturn(Version::fromInt(123));

        $toSectionConfig =
            SectionConfig::fromArray(
                [
                    'section' => [
                        'name' => 'nameTo',
                        'handle' => 'ToBeMapped',
                        'fields' => ['a', 'b'],
                        'default' => 'default',
                        'namespace' => 'nameFromSpace'
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
                    'handle' => 'mapper',
                    'fields' => ['a', 'v', 'b'],
                    'default' => 'def',
                    'namespace' => 'nameInSpace'
                ]
            ])
        ];

        $generated = DoctrineManyToManyGenerator::generate(
            $field,
            TemplateDir::fromString('src/FieldType/Relationship'),
            $options
        );

        $expected = <<<'EOT'
<many-to-many field="aliases_123" target-entity="nameFromSpace\Entity\ToBeMapped" inversed-by="mappers_37">
    <join-table name="mappers_37_aliases_123">
        <join-columns>
            <join-column name="mapper_37_id" referenced-column-name="id" />
        </join-columns>
        <inverse-join-columns>
            <join-column name="that_123_id" referenced-column-name="id" />
        </inverse-join-columns>
    </join-table>
</many-to-many>

EOT;

        $this->assertNotEmpty($generated);
        $this->assertInstanceOf(Template::class, $generated);
        $this->assertSame($expected, (string) $generated);
    }
}
