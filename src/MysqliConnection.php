<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB;

use Bitrix\Main\DB;
use Bitrix\Main\DB\SqlHelper;
use Bitrix\Main\Diag\SqlTrackerQuery;
use Bitrix\Main\ORM;

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
}
