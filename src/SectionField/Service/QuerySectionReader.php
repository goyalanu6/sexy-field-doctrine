<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\QueryComponents\From;
use Tardigrades\SectionField\QueryComponents\ManyToMany;
use Tardigrades\SectionField\QueryComponents\ManyToOne;
use Tardigrades\SectionField\QueryComponents\OneToMany;
use Tardigrades\SectionField\QueryComponents\QueryStructure;
use Tardigrades\SectionField\QueryComponents\Select;
use Tardigrades\SectionField\QueryComponents\Where;
use Tardigrades\SectionField\ValueObject\SectionConfig;

class QuerySectionReader
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\ORM\QueryBuilder */
    private $queryBuilder;

    /**
     * QuerySectionReader constructor.
     * @param EntityManagerInterface $entityManager
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        QueryBuilder $queryBuilder = null
    ) {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
        if (is_null($queryBuilder)) {
            $this->queryBuilder = $this->entityManager->createQueryBuilder();
        }
    }

    public function read(ReadOptionsInterface $readOptions, SectionConfig $sectionConfig = null): \ArrayIterator
    {
        $queryStructure = new QueryStructure($readOptions, $sectionConfig);
        $structure = $queryStructure->get();

        From::add($this->queryBuilder, $structure);
        Select::add($this->queryBuilder, $structure);
        OneToMany::add($this->queryBuilder, $structure);
        ManyToOne::add($this->queryBuilder, $structure);
        ManyToMany::add($this->queryBuilder, $structure);
        Where::add($this->queryBuilder, $structure);

        return new \ArrayIterator([]);
    }

    public function getDQL(): string
    {
        return $this->queryBuilder->getDQL();
    }
}
