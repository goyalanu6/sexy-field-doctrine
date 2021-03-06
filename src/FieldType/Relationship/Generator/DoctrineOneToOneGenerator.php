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

namespace Tardigrades\FieldType\Relationship\Generator;

use Doctrine\Common\Util\Inflector;
use Tardigrades\Entity\FieldInterface;
use Tardigrades\Entity\SectionInterface;
use Tardigrades\FieldType\Generator\GeneratorInterface;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\Generator\Loader\TemplateLoader;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\Handle;
use Tardigrades\SectionField\ValueObject\SectionConfig;

/**
 * Class DoctrineOneToOneGenerator
 *
 * @package Tardigrades\FieldTypeInterface\Relationship\Generator
 */
class DoctrineOneToOneGenerator implements GeneratorInterface
{
    const KIND = 'one-to-one';

    public static function generate(FieldInterface $field, TemplateDir $templateDir, ...$options): Template
    {
        $fieldConfig = $field->getConfig()->toArray();

        /** @var SectionManagerInterface $sectionManager */
        $sectionManager = $options[0]['sectionManager'];

        /** @var SectionConfig $sectionConfig */
        $sectionConfig = $options[0]['sectionConfig'];

        if ($fieldConfig['field']['kind'] === self::KIND) {

            /** @var SectionInterface $from */
            $from = $sectionManager->readByHandle($sectionConfig->getHandle());

            /** @var SectionInterface $to */
            $toHandle = $fieldConfig['field']['as'] ?? $fieldConfig['field']['to'];
            $to = $sectionManager->readByHandle(Handle::fromString($fieldConfig['field']['to']));

            $fromVersion = $from->getVersion()->toInt() > 1 ? ('_' . $from->getVersion()->toInt()) : '';
            $toVersion = $to->getVersion()->toInt() > 1 ? ('_' . $to->getVersion()->toInt()) : '';

            return Template::create(
                TemplateLoader::load(
                    (string) $templateDir . '/GeneratorTemplate/doctrine.onetoone.xml.php',
                    [
                        'type' => $fieldConfig['field']['relationship-type'],
                        'owner' => $fieldConfig['field']['owner'],
                        'toFullyQualifiedClassName' => $to->getConfig()->getFullyQualifiedClassName(),
                        'fromHandle' => $sectionConfig->getHandle() . $fromVersion,
                        'fromFullyQualifiedClassName' => $sectionConfig->getFullyQualifiedClassName(),
                        'toHandle' => $toHandle . $toVersion,
                        'cascade' => $fieldConfig['field']['cascade'] ?? false
                    ]
                )
            );
        }

        return Template::create('');
    }
}
