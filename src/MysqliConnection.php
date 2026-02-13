<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB;
use Bitrix\Main\DB\SqlHelper;
use Bitrix\Main\Diag\SqlTrackerQuery;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\ScalarField;

class MysqliConnection extends DB\MysqliConnection implements MappableInterface
{
    protected static array $tableMapping = [];

    protected function createSqlHelper(): SqlHelper
    {
        return new MysqliSqlHelper($this);
    }

    protected function createResult($result, ?SqlTrackerQuery $trackerQuery = null): DB\Result
    {
        return new MysqliResult($result, $this, $trackerQuery);
    }

    public function addTableMapping(string $tableName, array $map): void
    {
        if (array_key_exists($tableName, static::$tableMapping)) {
            return;
        }

        foreach ($map as $field) {
            if (!($field instanceof ORM\Fields\ScalarField)) {
                continue;
            }

            static::$tableMapping[$tableName][$field->getName()] = $field;
        }
    }

    public function getMappedField(string $tableName, string $fieldName): ?ORM\Fields\ScalarField
    {
        return static::$tableMapping[$tableName][$fieldName] ?? null;
    }

    public function createTable($tableName, $fields, $primary = [], $autoincrement = []): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->getSqlHelper()->quote($tableName) . ' (';
        $sqlFields = [];

        foreach ($fields as $columnName => $field) {
            if (!($field instanceof ScalarField)) {
                throw new ArgumentException(
                    sprintf(
                        'Field `%s` should be an Entity\ScalarField instance',
                        $columnName
                    )
                );
            }

            $realColumnName = $field->getColumnName();

            $sqlFields[] = $this->getSqlHelper()->quote($realColumnName)
                . ' ' . $this->getSqlHelper()->getColumnTypeByField($field)
                . ($field->isNullable() ? '' : ' NOT NULL') // null for oracle if is not primary
                . (in_array($columnName, $autoincrement, true) ? ' AUTO_INCREMENT' : '');
        }

        $sql .= implode(', ', $sqlFields);

        if (!empty($primary)) {
            foreach ($primary as &$primaryColumn) {
                $realColumnName = $fields[$primaryColumn]->getColumnName();
                $primaryColumn = $this->getSqlHelper()->quote($realColumnName);
            }
            unset($primaryColumn);

            $sql .= ', PRIMARY KEY(' . implode(', ', $primary) . ')';
        }

        foreach (array_diff((array) $autoincrement, (array) $primary) as $column) {
            $realColumnName = $fields[$column]->getColumnName();
            $sql .= ', UNIQUE KEY `idx_' . strtolower($realColumnName) . '` (`' . $realColumnName . '`)';
        }

        $sql .= ')';

        if ($this->engine) {
            $sql .= ' Engine=' . $this->engine;
        }

        $this->query($sql);
    }
}
