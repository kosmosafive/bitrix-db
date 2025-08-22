<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM;

use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM;

class Entity extends \Bitrix\Main\ORM\Entity
{
    /**
     * @throws SqlQueryException
     */
    public function createAdditionalIndexes(): void
    {
        if (!(new $this->className() instanceof Index\IndexableInterface)) {
            return;
        }

        $connection = $this->getConnection();

        foreach ($this->className::getIndexConfigurationCollection() as $indexConfiguration) {
            if ($connection->isIndexExists(
                $this->className::getTableName(),
                $indexConfiguration->getColumnList()
            )) {
                continue;
            }

            $connection->createIndex(
                $this->className::getTableName(),
                $indexConfiguration->getIndexName(),
                $indexConfiguration->getColumnList()
            );
        }
    }

    /**
     * @throws SqlQueryException
     */
    public function dropForeignKeys(): void
    {
        $connection = $this->getConnection();

        $sql = "
            SELECT
                CONSTRAINT_NAME
            FROM
                INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE
                TABLE_NAME = '{$this->className::getTableName()}'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ";

        $query = $connection->query($sql);
        while ($row = $query->fetch()) {
            $sql = "
                ALTER TABLE {$this->className::getTableName()}
                DROP FOREIGN KEY {$row['CONSTRAINT_NAME']};
            ";
            $connection->query($sql);
        }
    }

    /**
     * @throws SqlQueryException
     */
    public function createForeignKeys(): void
    {
        $connection = $this->getConnection();

        foreach ($this->className::getMap() as $field) {
            if (!($field instanceof ORM\Fields\Relations\Reference)) {
                continue;
            }

            $reference = $field->getReference();

            if ($reference instanceof ORM\Query\Filter\ConditionTree) {
                $conditions = [];

                foreach ($reference->getConditions() as $condition) {
                    $conditions[$condition->getDefinition()] = $condition->getValue()->getDefinition();
                }

                $reference = $conditions;
                unset($conditions);
            }

            if (!is_array($reference)) {
                continue;
            }

            $foreignKeyField = implode(', ', array_map(static fn ($value) => str_replace('this.', '', $value), array_keys($reference)));
            $referenceField = implode(', ', array_map(static fn ($value) => str_replace('ref.', '', $value), array_values($reference)));

            $referenceClassName = $field->getRefEntityName() . 'Table';

            $constraintName = 'fk_' . $this->className::getTableName() . '_' . $field->getName();

            $sql = "
            ALTER TABLE {$this->className::getTableName()}
            ADD CONSTRAINT {$constraintName}
            FOREIGN KEY ({$foreignKeyField})
            REFERENCES {$referenceClassName::getTableName()}({$referenceField})
            ";

            $connection->query($sql);
        }
    }
}
