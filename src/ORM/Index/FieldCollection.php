<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB\ORM\Index;

use Bitrix\Main\ORM\Fields\ScalarField;
use Kosmos\Bitrix\DB\Structure\Collection;

class FieldCollection extends Collection
{
    public function add(ScalarField ...$fieldList): FieldCollection
    {
        foreach ($fieldList as $field) {
            $this->values[] = $field;
        }

        return $this;
    }
}
