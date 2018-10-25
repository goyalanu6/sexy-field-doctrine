<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Tardigrades\FieldType\Generator;

use Tardigrades\Entity\FieldInterface;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\Generator\Loader\TemplateLoader;
use Tardigrades\SectionField\ValueObject\SectionConfig;

class DoctrineFieldGenerator implements GeneratorInterface
{
    public static function generate(FieldInterface $field, TemplateDir $templateDir, ...$options): Template
    {
        $asString = (string) TemplateLoader::load(
            (string) $templateDir
            . '/GeneratorTemplate/doctrine.config.xml.template'
        );

        $asString = str_replace(
            '{{ handle }}',
            $field->getConfig()->getHandle(),
            $asString
        );

        $fieldConfig = $field->getConfig()->toArray();

        $unique = false;
        try {
            $unique = $fieldConfig['field']['generator']['doctrine']['unique'];
        } catch (\Exception $exception) {
            // Unique not defined
        }

        try {
            /** @var SectionConfig $sectionConfig */
            $sectionConfig = $options[0]['sectionConfig'];
            $generatorConfig = $sectionConfig->getGeneratorConfig()->toArray();

            // If the key exists, it means it's overridden in the section config.
            // So let's do it again, set it to true.
            if (array_key_exists('unique', $generatorConfig['doctrine'][(string)$field->getHandle()])) {
                $unique = true;
                // unless it's set to false
                if (!$generatorConfig['doctrine'][(string)$field->getHandle()]['unique']) {
                    $unique = false;
                }
            }
        } catch (\Throwable $e) {
            // The key did't exist at all
        }

        $asString = str_replace(
            '{{ unique }}',
            $unique ? 'unique="true" ' : '',
            $asString
        );

        return Template::create($asString);
    }
}
