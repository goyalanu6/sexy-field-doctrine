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
        if (!empty($structure[QueryStructure::SELECT])) {
            foreach ($structure[QueryStructure::SELECT] as $select) {
                $add = $select['alias'];
                if (!empty($select[QueryStructure::HANDLE])) {
                    $add .= $select[QueryStructure::HANDLE];
                }
                $query->addSelect($add);
            }
        }
    }
}
