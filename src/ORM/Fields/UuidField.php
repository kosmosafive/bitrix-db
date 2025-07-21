<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB\ORM\Fields;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\StringField;
use Ramsey\Uuid\Uuid;

class UuidField extends StringField
{
    public function __construct($name, $parameters = [])
    {
        $this->addSaveDataModifier([$this, 'encode']);

        parent::__construct($name, $parameters);

        $this->addFetchDataModifier([$this, 'decode']);
    }

    public function encode($data): ?string
    {
        if (empty($data)) {
            return null;
        }

        return Uuid::fromString($data)->getBytes();
    }

    public function decode($data): ?string
    {
        if (empty($data)) {
            return null;
        }

        return Uuid::fromBytes($data)->toString();
    }

    public function convertValueToDb($value)
    {
        if ($value instanceof SqlExpression) {
            return $value;
        }

        if ($value === null && $this->is_nullable) {
            return $value;
        }

        if (!Uuid::isValid($value)) {
            $value = Uuid::fromBytes($value)->toString();
        }

        return "UUID_TO_BIN('{$value}')";
    }
}
