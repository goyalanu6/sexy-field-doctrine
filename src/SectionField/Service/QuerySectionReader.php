<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\QueryComponents\From;
use Tardigrades\SectionField\QueryComponents\Limit;
use Tardigrades\SectionField\QueryComponents\ManyToMany;
use Tardigrades\SectionField\QueryComponents\ManyToOne;
use Tardigrades\SectionField\QueryComponents\Offset;
use Tardigrades\SectionField\QueryComponents\OneToMany;
use Tardigrades\SectionField\QueryComponents\OneToOne;
use Tardigrades\SectionField\QueryComponents\OrderBy;
use Tardigrades\SectionField\QueryComponents\QueryStructure;
use Tardigrades\SectionField\QueryComponents\QueryStructureInterface;
use Tardigrades\SectionField\QueryComponents\Select;
use Tardigrades\SectionField\QueryComponents\Where;
use Tardigrades\SectionField\ValueObject\SectionConfig;

class QuerySectionReader
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\ORM\QueryBuilder */
    private $queryBuilder;

    /** @var QueryStructureInterface */
    private $queryStructure;

    /**
     * QuerySectionReader constructor.
     * @param EntityManagerInterface $entityManager
     * @param QueryBuilder $queryBuilder
     * @param QueryStructureInterface $queryStructure
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        QueryBuilder $queryBuilder = null,
        QueryStructureInterface $queryStructure
    ) {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
        $this->queryStructure = $queryStructure;

        if (is_null($queryBuilder)) {
            $this->queryBuilder = $this->entityManager->createQueryBuilder();
        }
    }

    public function read(ReadOptionsInterface $readOptions, SectionConfig $sectionConfig = null): \ArrayIterator
    {
        $structure = $this->queryStructure->get($readOptions, $sectionConfig);

        From::add($this->queryBuilder, $structure);
        Select::add($this->queryBuilder, $structure);
        foreach ($structure[QueryStructure::RELATIONSHIP] as $relationship) {
            switch ($relationship[QueryStructure::KIND]) {
                case OneToMany::ONE_TO_MANY:
                    OneToMany::add($this->queryBuilder, $relationship);
                    break;
                case ManyToOne::MANY_TO_ONE:
                    ManyToOne::add($this->queryBuilder, $relationship);
                    break;
                case ManyToMany::MANY_TO_MANY:
                    ManyToMany::add($this->queryBuilder, $relationship);
                    break;
                case OneToOne::ONE_TO_ONE:
                    OneToOne::add($this->queryBuilder, $relationship);
                    break;
            }
        }
        Where::add($this->queryBuilder, $structure);
        Limit::add($this->queryBuilder, $structure);
        Offset::add($this->queryBuilder, $structure);
        OrderBy::add($this->queryBuilder, $structure);

        $results = $this->getResults();

        return new \ArrayIterator([]);
    }

    private function getResults(): array
    {
        return $this->queryBuilder->getQuery()->getResult();
    }
}
