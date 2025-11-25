<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Constraint;

use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;

/**
 * @template-extends Collection<ConstraintInterface>
 */
class ConstraintCollection extends Collection
{
    /**
     * @param ConstraintInterface $value
     *
     * @return ConstraintCollection
     */
    public function add(mixed $value): ConstraintCollection
    {
        if (!$value instanceof ConstraintInterface) {
            throw new InvalidArgumentException("This collection only accepts instances of " . ConstraintInterface::class);
        }

        return parent::add($value);
    }
}
