<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

interface ConstraintableInterface
{
    public static function getConstraintCollection(): ConstraintCollection;
}
