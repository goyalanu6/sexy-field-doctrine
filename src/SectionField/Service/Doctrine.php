<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;

abstract class Doctrine
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var Registry */
    protected $doctrine;

    public function __construct(Registry $registry)
    {
        $this->doctrine = $registry;
        $this->entityManager = null;
    }

    /**
     * We may have multiple entity managers defined, determin which one we need for
     * the section we are requesting
     *
     * @param FullyQualifiedClassName $section
     * @throws NoEntityManagerFoundForSection
     */
    protected function determineEntityManager(FullyQualifiedClassName $section)
    {
        $managers = $this->doctrine->getManagers();
        /** @var EntityManagerInterface $manager */
        foreach ($managers as $manager) {
            foreach ($manager->getConfiguration()->getEntityNamespaces() as $namespace) {
                if (strpos((string)$section, $namespace) === 0) {
                    $this->entityManager = $manager;
                }
            }
        }

        if (is_null($this->entityManager)) {
            throw new NoEntityManagerFoundForSection();
        }
    }
}
