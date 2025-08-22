<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Data;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager as BaseDataManager;
use Kosmosafive\Bitrix\DB\MappableInterface;
use Kosmosafive\Bitrix\DB\ORM\Entity;

abstract class DataManager extends BaseDataManager
{
    protected static function addTableMapping(array $map): void
    {
        $connection = Application::getInstance()
            ->getConnectionPool()
            ->getConnection(static::getConnectionName());

        if ($connection instanceof MappableInterface) {
            $connection->addTableMapping(static::getTableName(), $map);
        }
    }

    public static function getEntityClass(): string
    {
        return Entity::class;
    }
}
