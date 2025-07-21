<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\Type;

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

        return parent::getColumnTypeByField($field);
    }
}
