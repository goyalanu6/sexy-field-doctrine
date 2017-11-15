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

    public function setUp()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
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
        $deleted = 'I am being deleted';

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
        $deleted = 'I am not being deleted';

        $this->entityManager->shouldReceive('remove')
            ->once()
            ->with($deleted)
            ->andThrowExceptions([new Mockery\Exception]);

        $this->entityManager->shouldReceive('flush')
            ->never();

        $deleter = new DoctrineSectionDeleter($this->entityManager);

        $this->assertFalse($deleter->delete($deleted));
    }
}
