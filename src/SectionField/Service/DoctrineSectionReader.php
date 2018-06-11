<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\Slug;
use Tardigrades\SectionField\ValueObject\After;
use Tardigrades\SectionField\ValueObject\Before;
use Tardigrades\SectionField\ValueObject\CreatedField;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;
use Tardigrades\SectionField\ValueObject\Id;
use Tardigrades\SectionField\ValueObject\Limit;
use Tardigrades\SectionField\ValueObject\Offset;
use Tardigrades\SectionField\ValueObject\OrderBy;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\SlugField;

class DoctrineSectionReader implements ReadSectionInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var QueryBuilder */
    private $queryBuilder;

    /** FetchFieldsQueryBuilder */
    private $fetchFieldsQueryBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        FetchFieldsQueryBuilder $fetchFieldsQueryBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->fetchFieldsQueryBuilder = $fetchFieldsQueryBuilder;
    }

    public function read(ReadOptionsInterface $readOptions, SectionConfig $sectionConfig = null): \ArrayIterator
    {
        $fetchFields = $readOptions->getFetchFields();
        $this->queryBuilder = $this->entityManager->createQueryBuilder();

        /** @var FullyQualifiedClassName $section */
        $section = $readOptions->getSection()[0];

        $formatResult = false;
        if (!is_null($fetchFields)) {
            $this->queryBuilder = $this->fetchFieldsQueryBuilder->getQuery($this->queryBuilder, $section, $fetchFields);
            $formatResult = true;
        } else {
            $this->addSectionToQuery($section);
        }

        $this->addIdToQuery($readOptions->getId(), $section);
        $this->addSlugToQuery(
            $readOptions->getSlug(),
            $sectionConfig->getSlugField(),
            $section
        );

        $this->addFieldToQuery(
            $readOptions->getField(),
            $section
        );
        $this->addLimitToQuery($readOptions->getLimit());
        $this->addOffsetToQuery($readOptions->getOffset());

        $this->addOrderByToQuery(
            $readOptions->getOrderBy(),
            $section
        );
        $this->addBeforeToQuery(
            $sectionConfig->getCreatedField(),
            $readOptions->getBefore(),
            $section
        );
        $this->addAfterToQuery(
            $sectionConfig->getCreatedField(),
            $readOptions->getAfter(),
            $section
        );
        $query = $this->queryBuilder->getQuery();

        $results = $query->getResult();

        if ($formatResult) {
            $results = $this->formatResult($results, $section);
        }

        if (count($results) <= 0) {
            throw new EntryNotFoundException();
        }

        return new \ArrayIterator($results);
    }

    private function formatResult(array $results, FullyQualifiedClassName $section): array
    {
        $className = lcfirst($section->getClassName());
        $parsed = [];
        foreach ($results as $fields) {
            $entry = [];
            foreach ($fields as $field=>&$value) {
                $field = str_replace($className . '_', '', $field);
                $pieces = explode('_', $field);
                foreach (array_reverse($pieces) as $piece) {
                    $value = [ $piece => $value ];
                }
                $entry = array_merge_recursive($value, $entry);
            }
            $parsed[] = $entry;
        }
        return $parsed;
    }

    private function addSectionToQuery(FullyQualifiedClassName $section): void
    {
        $className = lcfirst((string) $section->getClassName());
        $this->queryBuilder->select((string) $className);
        $this->queryBuilder->from((string) $section, (string) $className);
    }

    private function addIdToQuery(Id $id = null, FullyQualifiedClassName $section): void
    {
        if ($id instanceof Id) {
            $className = lcfirst((string) $section->getClassName());
            $this->queryBuilder->where((string) $className . '.id = :id');
            $this->queryBuilder->setParameter('id', $id->toInt());
        }
    }

    private function addSlugToQuery(
        Slug $slug = null,
        SlugField $slugField = null,
        FullyQualifiedClassName $section
    ): void {
        if ($slug instanceof Slug && $slugField instanceof SlugField) {
            $className = lcfirst((string) $section->getClassName());
            $this->queryBuilder->where((string) $className . '.' . (string) $slugField . '= :slug');
            $this->queryBuilder->setParameter('slug', (string)$slug);
        }
    }

    private function addFieldToQuery(
        array $fields = null,
        FullyQualifiedClassName $section
    ): void {

        if (!empty($fields)) {
            $className = lcfirst((string) $section->getClassName());
            foreach ($fields as $handle=>$fieldValue) {
                if (is_array($fieldValue)) {
                    $this->queryBuilder->andWhere(
                        $this->queryBuilder->expr()->in(
                            (string) $className . '.' . (string) $handle,
                            ':' . $handle
                        )
                    );
                    $this->queryBuilder->setParameter($handle, $fieldValue);
                } else {
                    try {
                        $this->queryBuilder->innerJoin(
                            (string)$className . '.' . $handle,
                            $handle,
                            'WITH',
                            $handle . '.id = ' . (string)$fieldValue
                        );
                    } catch (\Exception $exception) {
                        $this->queryBuilder->andWhere(
                            (string)$className . '.' . (string)$handle . '= :' . $handle
                        );
                        $this->queryBuilder->setParameter($handle, (string)$fieldValue);
                    }
                }
            }
        }
    }

    private function addLimitToQuery(Limit $limit = null): void
    {
        if ($limit instanceof Limit) {
            $this->queryBuilder->setMaxResults($limit->toInt());
        }
    }

    private function addOffsetToQuery(Offset $offset = null): void
    {
        if ($offset instanceof Offset) {
            $this->queryBuilder->setFirstResult($offset->toInt());
        }
    }

    private function addOrderByToQuery(OrderBy $orderBy = null, FullyQualifiedClassName $section = null): void
    {
        if ($orderBy instanceof OrderBy && $section instanceof FullyQualifiedClassName) {
            $className = lcfirst((string) $section->getClassName());
            $field = (string) $className . '.' . (string) $orderBy->getHandle();
            if (strpos((string) $orderBy->getHandle(), '.')) {
                $field = (string) $orderBy->getHandle();
            }
            $this->queryBuilder->orderBy(
                $field,
                (string) $orderBy->getSort()
            );
        }
    }

    private function addBeforeToQuery(
        CreatedField $createdField,
        Before $before = null,
        FullyQualifiedClassName $section = null
    ): void {
        if ($before instanceof Before && $section instanceof FullyQualifiedClassName) {
            $className = lcfirst((string) $section->getClassName());
            $this->queryBuilder->where($className . '.' . (string) $createdField . ' < :before');
            $this->queryBuilder->setParameter('before', (string) $before);
        }
    }

    private function addAfterToQuery(
        CreatedField $createdField,
        After $after = null,
        FullyQualifiedClassName $section = null
    ): void {
        if ($after instanceof After && $section instanceof FullyQualifiedClassName) {
            $className = lcfirst((string) $section->getClassName());
            $this->queryBuilder->where($className. '.' . (string) $createdField . ' > :after');
            $this->queryBuilder->setParameter('after', (string) $after);
        }
    }
}
