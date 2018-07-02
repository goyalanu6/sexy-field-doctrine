<?php
declare (strict_types=1);

namespace Tardigrades\SectionField\QueryComponents;

use Tardigrades\SectionField\Service\ReadOptionsInterface;
use Tardigrades\SectionField\ValueObject\FullyQualifiedClassName;
use Tardigrades\SectionField\ValueObject\Id;
use Tardigrades\SectionField\ValueObject\SectionConfig;
use Tardigrades\SectionField\ValueObject\Slug;

class QueryStructure
{
    /** @var ReadOptionsInterface */
    private $readOptions;

    /** @var SectionConfig */
    private $sectionConfig;

    /** @var array */
    private $structure;

    /** @var FullyQualifiedClassName $section */
    private $section;

    /**
     * QueryStructure constructor.
     * @param ReadOptionsInterface $readOptions
     * @param SectionConfig|null $sectionConfig
     */
    public function __construct(
        ReadOptionsInterface $readOptions,
        SectionConfig $sectionConfig = null
    ) {
        $this->readOptions = $readOptions;
        $this->section = $readOptions->getSection()[0];
        $this->sectionConfig = $sectionConfig;
        $this->structure = [];
    }

    public function get(): \ArrayIterator
    {
        /** @var array $fetchFields */
        $fetchFields = $this->readOptions->getFetchFields();
        $fullyQualifiedClassName = (string) $this->section;
        $entityProperties = $fullyQualifiedClassName::FIELDS;
        $this->structure['from'] = $this->section;

        if (!is_null($fetchFields)) {
            $this->structure = $this->find(
                $fetchFields,
                $entityProperties,
                $fullyQualifiedClassName,
                $this->structure
            );
        } else {
            $this->structure['select'][] = [
                'fullyQualifiedClassName' => $this->section,
                'handle' => ''
            ];
        }

        $this->addWhere();

        return new \ArrayIterator($this->structure);
    }

    private function find(
        array $fetchFields,
        array $entityProperties,
        string $fullyQualifiedClassName,
        array &$structure
    ): array {

        foreach ($entityProperties as $fieldProperty => $field) {
            if ($field['type'] === 'Slug') {
                if (in_array('slug', $fetchFields)) {
                    $fetchFields[] = $fieldProperty;
                }
            }
        }

        foreach ($entityProperties as $fieldProperty => $field) {
            if (in_array($fieldProperty, $fetchFields)) {
                if (!empty($field['relationship'])) {
                    if (empty($structure[$field['relationship']['kind']])) {
                        $structure[$field['relationship']['kind']] = [];
                    }
                    $structure[$field['relationship']['kind']][] =
                        FullyQualifiedClassName::fromString($field['relationship']['class']);
                    $fullyQualifiedClassName = $field['relationship']['class'];
                    $entityProperties = $fullyQualifiedClassName::FIELDS;
                    $this->find($fetchFields, $entityProperties, $fullyQualifiedClassName, $structure);
                } else {
                    if (empty($structure['select'])) {
                        $structure['select'] = [];
                    }
                    $select = [
                        'fullyQualifiedClassName' => FullyQualifiedClassName::fromString($fullyQualifiedClassName),
                        'handle' => $field['handle']
                    ];
                    $structure['select'][] = $select;
                }
            }
        }

        return $structure;
    }

    private function addWhere(): void
    {
        if (empty($this->structure['where'])) {
            $this->structure['where'] = [];
        }

        $this->addWhereId();
        $this->addWhereSlug();

        $field = $this->readOptions->getField();

        if (!is_null($field)) {
            if (!is_array($field[key($field)])) {
                $this->addOneWhereField();
            } else {
                $this->addWhereInField();
            }
        }
    }

    private function addWhereId(): void
    {
        /** @var Id $id */
        $id = $this->readOptions->getId();
        if (!is_null($id)) {
            $this->structure['where'][] = [
                'fullyQualifiedClassName' => $this->section,
                'parameters' => [
                    'key' => 'id',
                    'property' => 'id',
                    'value' => $id->toInt()
                ]
            ];
        }
    }

    private function addWhereSlug(): void
    {
        /** @var Slug $slug */
        $slug = $this->readOptions->getSlug();
        if (!is_null($slug)) {
            $this->structure['where'][] = [
                'fullyQualifiedClassName' => $this->section,
                'parameters' => [
                    'key' => 'slug',
                    'property' => 'slug',
                    'value' => (string) $slug
                ]
            ];
        }
    }

    private function addOneWhereField(): void
    {
        $field = $this->readOptions->getField();
        if (!is_null($field)) {
            $this->structure['where'][] = [
                'fullyQualifiedClassName' => $this->section,
                'parameters' => [
                    'key' => 'field',
                    'property' => key($field),
                    'value' => $field[key($field)]
                ]
            ];
        }
    }

    private function addWhereInField(): void
    {
        $field = $this->readOptions->getField();
        if (!is_null($field)) {
            $this->structure['where'][] = [
                'fullyQualifiedClassName' => $this->section,
                'parameters' => [
                    'key' => 'fields',
                    'property' => key($field),
                    'value' => $field[key($field)]
                ]
            ];
        }
    }
}
