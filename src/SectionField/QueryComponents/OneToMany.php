<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class OneToMany implements ComponentInterface
{
    const ONE_TO_MANY = 'one-to-many';

    public static function add(
        QueryBuilder $query,
        array $relationship
    ): void {
        $query->leftJoin(
            (string)$relationship['to'],
            $relationship['as']
        );
    }
}
