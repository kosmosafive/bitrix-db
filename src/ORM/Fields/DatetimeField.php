<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB\ORM\Fields;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB\SqlExpression;

class DatetimeField extends \Bitrix\Main\ORM\Fields\DatetimeField
{
    protected int $size = 0;

    public function __construct($name, array $parameters = [])
    {
        parent::__construct($name, $parameters);

        if (isset($parameters['size']) && (int) $parameters['size'] > 0) {
            $this->size = (int) $parameters['size'];
        }
    }

    public function configureSize($size): DatetimeField
    {
        $this->size = (int) $size;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function convertValueToDb($value)
    {
        if ($value instanceof SqlExpression) {
            return $value;
        }

        try {
            if (
                ($value === null)
                && $this->is_nullable
            ) {
                return null;
            }

            $sqlHelper = $this->getConnection()->getSqlHelper();

            if (
                ($this->size > 0)
                && method_exists($sqlHelper, 'convertToDbDateTimeWithMicro')
            ) {
                return $sqlHelper->convertToDbDateTimeWithMicro($value);
            }

            return $sqlHelper->convertToDbDateTime($value);
        } catch (ArgumentTypeException $e) {
            $exceptionMsg = $this->entity
                ? "Type error in `{$this->name}` of `{$this->entity->getFullName()}`"
                : "Type error in `{$this->name}`";

            throw new ArgumentException(
                "{$exceptionMsg}: {$e->getMessage()}"
            );
        }
    }
}
