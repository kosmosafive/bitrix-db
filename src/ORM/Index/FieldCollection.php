<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

use Bitrix\Main\ORM\Fields\ScalarField;
use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;

/**
 * @template-extends Collection<ScalarField>
 */
class FieldCollection extends Collection
{
    /**
     * @param ScalarField $value
     * @return FieldCollection
     */
    public function add(mixed $value): FieldCollection
    {
        if (!$value instanceof ScalarField) {
            throw new InvalidArgumentException("This collection only accepts instances of " . ScalarField::class);
        }

        return parent::add($value);
    }
}
