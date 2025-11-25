<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

abstract readonly class Constraint implements ConstraintInterface
{
    protected string $name;

    protected const string PREFIX = '';

    public function __construct(
        string $name
    ) {
        $this->name = $this->generateName($name);
    }

    protected function generateName(string $name): string
    {
        $prefix = static::PREFIX . '_';
        if (!str_starts_with($name, $prefix)) {
            $name = $prefix . $name;
        }

        return $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
