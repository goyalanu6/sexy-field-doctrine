<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class From implements ComponentInterface
{
    public static function add(QueryBuilder $query, \ArrayIterator $structure): void
    {
        /** @var FullyQualifiedClassName $section */
        $section = $structure['from'];
        $query->from((string) $section, $section->getClassName());
    }
}
