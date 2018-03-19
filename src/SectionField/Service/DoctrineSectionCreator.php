<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tardigrades\SectionField\Generator\CommonSectionInterface;

class DoctrineSectionCreator implements CreateSectionInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function save(CommonSectionInterface $data)
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function persist(CommonSectionInterface $data)
    {
        $this->entityManager->persist($data);
    }

    public function flush()
    {
        $this->entityManager->flush();
    }
}
