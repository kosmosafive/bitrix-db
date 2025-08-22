<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Index;

interface IndexableInterface
{
    public static function getIndexConfigurationCollection(): ConfigurationCollection;
}
