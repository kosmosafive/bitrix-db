<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

readonly class Check extends Constraint
{
    protected const string PREFIX = 'chk';
    public function __construct(
        string $name,
        protected string $condition
    ) {
        parent::__construct($name);
    }

    public function getCondition(): string
    {
        return "CHECK ($this->condition)";
    }
}
