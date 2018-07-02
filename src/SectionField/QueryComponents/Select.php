<?php

declare (strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class Select implements ComponentInterface
{
    public static function add(
        QueryBuilder $query,
        \ArrayIterator $structure
    ): void {
        if (!empty($structure['select'])) {
            foreach ($structure['select'] as $select) {
                /** @var FullyQualifiedClassName $fullyQualifiedClassName */
                $fullyQualifiedClassName = $select['fullyQualifiedClassName'];
                $add = $fullyQualifiedClassName->getClassName();
                if (!empty($select['handle'])) {
                    $add .= '.' . $select['handle'];
                }
                $query->addSelect($add);
            }
        }
    }
}
