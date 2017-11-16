<?php
declare (strict_types=1);

namespace Tardigrades\FieldType\Generator;

use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tardigrades\Entity\Field;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\ValueObject\FieldConfig;

/**
 * @coversDefaultClass Tardigrades\FieldType\Generator\DoctrineFieldGenerator
 * @covers ::<private>
 */
final class DoctrineFieldGeneratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @covers ::generate
     */
    public function it_generates()
    {
        $mockedFieldInterface = Mockery::mock(new Field())->makePartial();
        $templateDir = TemplateDir::fromString('src/SectionField/Generator');

        $mockedFieldInterface->shouldReceive('getConfig')
            ->andReturn(
                FieldConfig::fromArray(
                    [
                        'field' => [
                            'name' => 'iets',
                            'handle' => 'niets',
                            'kind' => '12345',
                            'entityEvents' => ['1', '2']
                        ]
                    ]
                )
            );

        $generatedTemplate = DoctrineFieldGenerator::generate($mockedFieldInterface, $templateDir);
        $this->assertInstanceOf(Template::class, $generatedTemplate);
        $this->assertNotEmpty($generatedTemplate);
    }
}
