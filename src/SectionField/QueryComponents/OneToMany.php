<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class OneToMany implements ComponentInterface
{
    public static function add(
        QueryBuilder $query,
        \ArrayIterator $structure
    ): void {
        if (!empty($structure['one-to-many'])) {
            /** @var FullyQualifiedClassName $relate */
            foreach ($structure['one-to-many'] as $relate) {
                $query->leftJoin((string) $relate, $relate->getClassName());
            }
        }
    }
}
