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

class DoctrineFieldGenerator implements GeneratorInterface
{
    public static function generate(FieldInterface $field, TemplateDir $templateDir): Template
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

        $asString = str_replace(
            '{{ unique }}',
            $unique ? 'unique="true" ' : '',
            $asString
        );

        return Template::create($asString);
    }
}
