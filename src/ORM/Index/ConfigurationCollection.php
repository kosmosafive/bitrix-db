<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

use InvalidArgumentException;
use Kosmosafive\Bitrix\DS\Collection;

/**
 * @template-extends Collection<ConfigurationCollection>
 */
class ConfigurationCollection extends Collection
{
    public function __construct(
        protected readonly string $className
    ) {
        parent::__construct();
    }

    public function add(mixed $value, ?string $indexName = null): ConfigurationCollection
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException("Value must be an array");
        }

        return parent::add(new Configuration($this->className, $value, $indexName));
    }
}
