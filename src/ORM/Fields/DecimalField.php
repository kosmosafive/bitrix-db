<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Fields;

use BcMath\Number;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\DecimalField as BitrixDecimalField;

class DecimalField extends BitrixDecimalField
{
    public function cast($value)
    {
        if ($this->is_nullable && ($value === null)) {
            return $value;
        }

        if ($value instanceof SqlExpression) {
            return $value;
        }

        $valueNumber = new Number((string)$value);

        if ($this->scale !== null) {
            $valueNumber = $valueNumber->round((int)$this->scale);
        }

        return (string)$valueNumber;
    }

    public function getGetterTypeHint()
    {
        return $this->getNullableTypeHint('\\string');
    }


    public function getSetterTypeHint()
    {
        return $this->getNullableTypeHint('\\string');
    }

    public function convertValueFromDb($value)
    {
        return $value;
    }

    public function convertValueToDb($value)
    {
        if ($value instanceof SqlExpression) {
            return $value;
        }

        if ($this->is_nullable && ($value === null)) {
            return $value;
        }

        $valueNumber = new Number((string)$value);
        return "'" . (string)$valueNumber . "'";
    }
}
