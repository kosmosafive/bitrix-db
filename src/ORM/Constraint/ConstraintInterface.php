<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

interface ConstraintInterface
{
    public function getName(): string;
    public function getCondition(): string;
}