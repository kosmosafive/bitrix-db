<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

use Bitrix\Main\ORM\Fields\ScalarField;

class Configuration
{
    protected ?FieldCollection $fieldCollection;

    public function __construct(
        string $className,
        array $fieldList,
        protected readonly ?string $indexName = null
    ) {
        $this->fieldCollection = new FieldCollection();

        $fieldMap = array_fill_keys(array_unique($fieldList), null);
        foreach ($className::getMap() as $field) {
            if (
                !($field instanceof ScalarField)
                || !array_key_exists($field->getName(), $fieldMap)
            ) {
                continue;
            }

            $fieldMap[$field->getName()] = $field;
        }

        $this->fieldCollection->add(...array_filter($fieldMap, static fn ($value) => !empty($value)));
    }

    public function getColumnList(): array
    {
        $columnList = [];

        foreach ($this->fieldCollection as $field) {
            $columnList[] = $field->getName();
        }

        return $columnList;
    }

    public function getIndexName(): string
    {
        return ($this->indexName) ?: hash('crc32', implode('#', $this->getColumnList()));
    }
}
