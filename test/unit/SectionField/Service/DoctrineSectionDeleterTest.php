<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * @coversDefaultClass Tardigrades\SectionField\Service\DoctrineSectionDeleter
 * @covers ::<private>
 */
final class DoctrineSectionDeleterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var EntityManagerInterface|Mockery\Mock */
    private $entityManager;

    /** @var DoctrineSectionDeleter */
    private $deleter;

    public function setUp()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->deleter = new DoctrineSectionDeleter($this->entityManager);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function it_creates()
    {
        $deleter = new DoctrineSectionDeleter($this->entityManager);
        $this->assertInstanceOf(DoctrineSectionDeleter::class, $deleter);
    }

    /**
     * @test
     * @covers ::delete
     */
    public function it_deletes()
    {
        $deleted = Mockery::mock('alias:Tardigrades\SectionField\Generator\CommonSectionInterface')->makePartial();

        $this->entityManager->shouldReceive('remove')
            ->once()
            ->with($deleted)
            ->andReturn(true);

        $this->entityManager->shouldReceive('flush')
            ->andReturn(true);

        $deleter = new DoctrineSectionDeleter($this->entityManager);

        $this->assertTrue($deleter->delete($deleted));
    }

    /**
     * @test
     * @covers ::delete
     */
    public function it_does_not_delete()
    {
        $deleted = Mockery::mock('alias:Tardigrades\SectionField\Generator\CommonSectionInterface')->makePartial();

        $this->entityManager->shouldReceive('remove')
            ->once()
            ->with($deleted)
            ->andThrowExceptions([new Mockery\Exception]);

        $this->entityManager->shouldReceive('flush')
            ->never();

        $deleter = new DoctrineSectionDeleter($this->entityManager);

        $this->assertFalse($deleter->delete($deleted));
    }

    /**
     * @test
     * @covers ::remove
     */
    public function it_removes()
    {
        $entry = Mockery::mock('alias:Tardigrades\SectionField\Generator\CommonSectionInterface')->makePartial();
        $this->entityManager->shouldReceive('remove')->with($entry)->once();
        $this->deleter->remove($entry);
    }

    /**
     * @test
     * @covers ::flush
     */
    public function it_flushes()
    {
        $this->entityManager->shouldReceive('flush')->once();
        $this->deleter->flush();
    }
}
