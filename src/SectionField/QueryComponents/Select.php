<?php

declare (strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;

class Select implements ComponentInterface
{
    public static function add(
        QueryBuilder $query,
        array $structure
    ): void {
        if (!empty($structure['select'])) {
            foreach ($structure['select'] as $select) {
                $add = $select['alias'];
                if (!empty($select['handle'])) {
                    $add .= $select['handle'];
                }
                $query->addSelect($add);
            }
        }
    }
}
