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
    protected $entityManager = null;

    /** @var Registry */
    protected $doctrine;

    public function __construct(Registry $registry)
    {
        $this->doctrine = $registry;
    }

    /**
     * We may have multiple entity managers defined, determine which
     * one we need for the section we are requesting
     *
     * @param FullyQualifiedClassName $section
     */
    protected function determineEntityManager(FullyQualifiedClassName $section = null)
    {
        if (!is_null($section)) {
            $managers = $this->doctrine->getManagers();
            /** @var EntityManagerInterface $manager */
            foreach ($managers as $manager) {
                $namespaces = $manager->getConfiguration()->getEntityNamespaces();
                foreach ($namespaces as $namespace) {
                    if (strpos((string)$section, $namespace) === 0) {
                        $this->entityManager = $manager;
                    }
                }
            }
        }

        if (is_null($this->entityManager)) {
            $this->entityManager = $this->doctrine->getManager($this->doctrine->getDefaultManagerName());
        }
    }
}
