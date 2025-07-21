<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB;

use Bitrix\Main\ORM;

interface MappableInterface
{
    public function addTableMapping(string $tableName, array $map): void;
    public function getMappedField(string $tableName, string $fieldName): ?ORM\Fields\ScalarField;
}
