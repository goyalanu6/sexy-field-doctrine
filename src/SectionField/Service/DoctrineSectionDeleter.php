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

class DoctrineSectionDeleter implements DeleteSectionInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function delete(CommonSectionInterface $sectionEntryEntity): bool
    {
        try {
            $this->entityManager->remove($sectionEntryEntity);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
