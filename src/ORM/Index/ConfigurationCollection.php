<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

use Kosmosafive\Bitrix\DB\Structure\Collection;

class ConfigurationCollection extends Collection
{
    public function __construct(
        protected readonly string $className
    ) {
        parent::__construct();
    }

    public function add(array $fieldList, ?string $indexName = null): ConfigurationCollection
    {
        $this->values[] = new Configuration($this->className, array_unique($fieldList), $indexName);
        return $this;
    }
}
