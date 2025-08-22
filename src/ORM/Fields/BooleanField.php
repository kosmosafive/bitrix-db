<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Fields;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\ScalarField;

class BooleanField extends ScalarField
{
    public function __construct($name, $parameters = [])
    {
        parent::__construct($name, $parameters);

        $this->addSaveDataModifier([$this, 'normalizeValue']);
    }

    public function cast($value)
    {
        if ($this->is_nullable && $value === null) {
            return null;
        }

        if ($value instanceof SqlExpression) {
            return $value;
        }

        return $this->booleanizeValue($value);
    }

    public function normalizeValue($value): ?int
    {
        if ($value === null) {
            return null;
        }

        return $this->booleanizeValue($value) ? 1 : 0;
    }

    public function booleanizeValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return (bool) $value;
    }

    public function convertValueFromDb($value): bool
    {
        return $this->booleanizeValue($value);
    }

    public function convertValueToDb($value): SqlExpression|int|string|null
    {
        if ($value instanceof SqlExpression) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return $value ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getGetterTypeHint(): string
    {
        return $this->getNullableTypeHint('\\boolean');
    }

    /**
     * @return string
     */
    public function getSetterTypeHint(): string
    {
        return $this->getNullableTypeHint('\\boolean');
    }

    public function isValueEmpty($value): bool
    {
        return ((string)$value === '' && $value !== false);
    }
}
