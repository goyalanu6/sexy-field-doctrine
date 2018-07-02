<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class ManyToOne implements ComponentInterface
{
    public static function add(
        QueryBuilder $query,
        \ArrayIterator $structure
    ): void
    {
        if (!empty($structure['many-to-one'])) {
            /** @var FullyQualifiedClassName $relate */
            foreach ($structure['many-to-one'] as $relate) {
                $query->join((string) $relate, $relate->getClassName());
            }
        }
    }
}
