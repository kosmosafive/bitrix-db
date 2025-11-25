<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

readonly class Unique extends Constraint
{
    protected array $columns;

    protected const string PREFIX = 'uq';

    public function __construct(
        string $name,
        string ...$columns
    ) {
        parent::__construct($name);

        $this->columns = array_filter(array_map(static fn(string $column): string => trim($column), $columns));
    }


    public function getCondition(): string
    {
        $columns = implode(', ', $this->columns);

        return "UNIQUE ($columns)";
    }
}