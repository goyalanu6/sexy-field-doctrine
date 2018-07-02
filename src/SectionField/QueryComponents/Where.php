<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Doctrine\ORM\QueryBuilder;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

class Where implements ComponentInterface
{
    public static function add(
        QueryBuilder $query,
        \ArrayIterator $structure
    ): void {
        if ($structure['where']) {
            foreach ($structure['where'] as $where) {
                /** @var FullyQualifiedClassName $fullyQualifiedClassName */
                $fullyQualifiedClassName = $where['fullyQualifiedClassName'];
                if (!is_array($where['parameters']['value'])) {
                    $query->andWhere(
                        $fullyQualifiedClassName->getClassName() . '.' .
                        $where['parameters']['property'] . ' = :' .
                        $where['parameters']['key']
                    );
                    $query->setParameter($where['parameters']['key'], $where['parameters']['value']);
                } else {
                    $query->andWhere(
                        $query->expr()->in(
                            $fullyQualifiedClassName->getClassName() . '.' . $where['parameters']['property'],
                            ':' . $where['parameters']['key']
                        )
                    );
                    $query->setParameter($where['parameters']['key'], $where['parameters']['value']);
                }
            }
        }
    }
}
