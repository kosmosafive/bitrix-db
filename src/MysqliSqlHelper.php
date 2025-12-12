<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Data\DataManager as BitrixDataManager;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type;
use InvalidArgumentException;
use Kosmosafive\Bitrix\DB\ORM\Data\DataManager;

class MysqliSqlHelper extends DB\MysqliSqlHelper
{
    /**
     * @throws ObjectException
     */
    public function convertFromDbDateTime($value): ?Type\DateTime
    {
        if (($value !== null) && ($value !== '0000-00-00 00:00:00')) {
            return new Type\DateTime($value, "Y-m-d H:i:s.u");
        }

        return null;
    }

    /**
     * @throws ArgumentTypeException
     */
    public function convertToDbDateTimeWithMicro($value): string
    {
        if (empty($value)) {
            return "NULL";
        }

        if ($value instanceof Type\Date) {
            if ($value instanceof Type\DateTime) {
                $value = clone $value;
                $value->setDefaultTimeZone();
            }
            return $this->getCharToDateFunction($value->format("Y-m-d H:i:s.u"));
        }

        throw new ArgumentTypeException('value', '\Bitrix\Main\Type\Date');
    }

    /**
     * @throws ArgumentTypeException
     * @throws SystemException
     */
    public function getColumnTypeByField(ScalarField $field): string
    {
        if ($field instanceof ORM\Fields\DatetimeField) {
            if ($field->getSize() > 0) {
                return 'datetime(' . $field->getSize() . ')';
            }

            return 'datetime';
        }

        if ($field instanceof ORM\Fields\UuidField) {
            return 'binary(16)';
        }

        if ($field instanceof ORM\Fields\BooleanField) {
            return 'boolean';
        }

        if ($field instanceof ORM\Fields\CharField) {
            $defaultLength = false;
            foreach ($field->getValidators() as $validator) {
                if ($validator instanceof Validators\LengthValidator) {
                    if ($defaultLength === false || $defaultLength > $validator->getMax()) {
                        $defaultLength = $validator->getMax();
                    }
                }
            }
            return 'char(' . ($defaultLength > 0 ? $defaultLength : 255) . ')';
        }

        if ($field instanceof ORM\Fields\UnsignedIntegerField) {
            $typeBySize = match ($field->getSize()) {
                2 => 'smallint',
                8 => 'bigint',
                default => 'int'
            };

            return $typeBySize . ' unsigned';
        }

        return parent::getColumnTypeByField($field);
    }

    /**
     * @param class-string<BitrixDataManager> $tableClass
     */
    public function prepareMergeByTableClass(string $tableClass, array $primaryFields, array $insertFields, array $updateFields)
    {
        $this->loadTableMap($tableClass);

        return $this->prepareMerge($tableClass::getTableName(), $primaryFields, $insertFields, $updateFields);
    }

    /**
     * @param class-string<BitrixDataManager> $tableClass
     *
     * @throws ArgumentException
     */
    public function prepareMergeValuesByTableClass(string $tableClass, array $primaryFields, array $insertRows, array $updateFields = []): string
    {
        $this->loadTableMap($tableClass);

        return $this->prepareMergeValues($tableClass::getTableName(), $primaryFields, $insertRows, $updateFields);
    }

    /**
     * @param class-string<BitrixDataManager> $tableClass
     */
    public function prepareMergeSelectByTableClass(string $tableClass, array $primaryFields, array $selectFields, $select, $updateFields)
    {
        $this->loadTableMap($tableClass);

        return $this->prepareMergeSelect(
            $tableClass::getTableName(),
            $primaryFields,
            $selectFields,
            $select,
            $updateFields
        );
    }

    /**
     * @param class-string<BitrixDataManager> $tableClass
     */
    public function prepareMergeMultipleByTableClass(string $tableClass, array $primaryFields, array $insertRows)
    {
        $this->loadTableMap($tableClass);

        return $this->prepareMergeMultiple($tableClass::getTableName(), $primaryFields, $insertRows);
    }

    protected function loadTableMap(string $tableClass): void
    {
        if (!is_subclass_of($tableClass, BitrixDataManager::class)) {
            throw new InvalidArgumentException($tableClass . " not implement " . BitrixDataManager::class);
        }

        if (is_subclass_of($tableClass, DataManager::class)) {
            $tableClass::getMap();
        }
    }
}
