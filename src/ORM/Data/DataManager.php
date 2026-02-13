<?php

declare(strict_types=1);

namespace Kosmosafive\Bitrix\DB\ORM\Data;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager as BaseDataManager;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Objectify\Values;
use Kosmosafive\Bitrix\DB\MappableInterface;
use Kosmosafive\Bitrix\DB\ORM\Entity;
use Kosmosafive\Bitrix\DB\ORM\Fields\UuidField;

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

    public static function add(array $data)
    {
        global $USER_FIELD_MANAGER;

        // compatibility
        $fields = $data;

        // prepare entity object for compatibility with new code
        $object = static::convertArrayToObject($fields, true);

        $entity = static::getEntity();
        $result = new AddResult();

        try
        {
            static::callOnBeforeAddEvent($object, $fields, $result);

            // actualize old-style fields array from object
            $fields = $object->collectValues(Values::CURRENT, FieldTypeMask::SCALAR);

            // uf values
            $ufdata = $object->collectValues(Values::CURRENT, FieldTypeMask::USERTYPE);

            // check data
            static::checkFields($result, null, $fields);

            // check uf data
            if (!empty($ufdata))
            {
                static::checkUfFields($object, $ufdata, $result);
            }

            // check if there is still some data
            if (empty($fields) && empty($ufdata))
            {
                $result->addError(new EntityError("There is no data to add."));
            }

            // return if any error
            if (!$result->isSuccess(true))
            {
                return $result;
            }

            //event on adding
            self::callOnAddEvent($object, $fields, $ufdata);

            // use save modifiers
            $fieldsToDb = $fields;

            foreach ($fieldsToDb as $fieldName => $value)
            {
                $field = $entity->getField($fieldName);
                if ($field->isPrimary() && $field->isAutocomplete() && is_null($value))
                {
                    unset($fieldsToDb[$fieldName]); // postgresql compatibility
                    continue;
                }
                $fieldsToDb[$fieldName] = $field->modifyValueBeforeSave($value, $fields);
            }

            // save data
            $connection = $entity->getConnection();

            $tableName = $entity->getDBTableName();
            $identity = $entity->getAutoIncrement();

            $dataReplacedColumn = static::replaceFieldName($fieldsToDb);
            $id = $connection->add($tableName, $dataReplacedColumn, $identity);

            // build standard primary
            $primary = null;
            $isGuessedPrimary = false;

            if (!empty($id))
            {
                if($entity->getAutoIncrement() <> '')
                {
                    $primary = array($entity->getAutoIncrement() => $id);
                    static::normalizePrimary($primary);
                }
                else
                {
                    $field = $entity->getField('ID');
                    if ($field instanceof UuidField) {
                        static::normalizePrimary($primary, $fields);
                    } else {
                        // for those who did not set 'autocomplete' flag but wants to get id from result
                        $primary = array('ID' => $id);
                        $isGuessedPrimary = true;
                    }
                }
            }
            else
            {
                static::normalizePrimary($primary, $fields);
            }

            // fill result
            $result->setPrimary($primary);
            $result->setData($fields + $ufdata);
            $result->setObject($object);

            if (!$isGuessedPrimary)
            {
                foreach ($primary as $primaryName => $primaryValue)
                {
                    $object->sysSetActual($primaryName, $primaryValue);
                }
            }

            // save uf data
            if (!empty($ufdata))
            {
                $ufUserId = false;

                if ($object->authContext)
                {
                    $ufUserId = $object->authContext->getUserId();
                }

                $USER_FIELD_MANAGER->update($entity->getUfId(), end($primary), $ufdata, $ufUserId);
            }

            static::cleanCache();

            static::callOnAfterAddEvent($object, $fields + $ufdata, $id);
        }
        catch (\Exception $e)
        {
            // check result to avoid warning
            $result->isSuccess();

            throw $e;
        }

        return $result;
    }
}
