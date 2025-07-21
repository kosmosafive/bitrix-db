<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB;

use Bitrix\Main\DB;
use Bitrix\Main\ORM\Fields\ScalarField;

class MysqliResult extends DB\MysqliResult
{
    /** @var ScalarField[] */
    private ?array $resultFields = null;

    public function getFields(): array
    {
        if ($this->resultFields === null) {
            $this->resultFields = [];
            if (is_object($this->resource)) {
                $fields = $this->resource->fetch_fields();
                if ($fields && $this->connection) {
                    $helper = $this->connection->getSqlHelper();
                    foreach ($fields as $field) {

                        if ($this->connection instanceof MappableInterface) {
                            $ormField = $this->connection->getMappedField($field->orgtable, $field->name);
                            if ($ormField) {
                                $this->resultFields[$field->name] = $ormField;
                                continue;
                            }
                        }

                        $this->resultFields[$field->name] = $helper->getFieldByColumnType(
                            $field->name ?: '(empty)',
                            $field->type,
                            (array) $field
                        );
                    }
                }
            }
        }

        return $this->resultFields;
    }
}
