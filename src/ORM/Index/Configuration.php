<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ScalarField;
use InvalidArgumentException;

class Configuration
{
    protected ?FieldCollection $fieldCollection;
    protected readonly ?string $indexName;

    public function __construct(
        /**
         * @var class-string<DataManager> $className
         */
        string $className,
        array $fieldList,
        ?string $indexName = null
    ) {
        if (!is_subclass_of($className, DataManager::class)) {
            throw new InvalidArgumentException("The class " . $className . " must be a subclass of " . DataManager::class);
        }

        $fieldList = array_filter(array_unique($fieldList));

        if ($indexName) {
            $indexName = trim($indexName);
        }
        $this->indexName = $indexName;

        $this->fieldCollection = new FieldCollection();

        $fieldMap = array_fill_keys($fieldList, null);
        foreach ($className::getMap() as $field) {
            if (
                !($field instanceof ScalarField)
                || !array_key_exists($field->getName(), $fieldMap)
            ) {
                continue;
            }

            $fieldMap[$field->getName()] = $field;
        }

        foreach ($fieldMap as $field) {
            $this->fieldCollection->add($field);
        }
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
