# Bitrix Database

Расширение соединения с базой данных Bitrix (MysqliConnection).

## Конфигурация

Конфигурацию указывать в файле /bitrix/.settings.php или /bitrix/.settings_extra.php.

```php
$vendorAutoload = dirname(__DIR__) . '/local/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// ...
'connections' => [
            'value' => [
                    'default' => [
                            'className' => '\\Kosmosafive\\Bitrix\\DB\\MysqliConnection',
// ...
```

## Использование

В качества класса DataManager используется `Kosmosafive\Bitrix\DB\ORM\Data\DataManager`.

### Индексы

В классе, описывающем таблицу (наследник `Kosmosafive\Bitrix\DB\ORM\Data\DataManager`), 
необходимо реализовать интерфейс `Kosmosafive\Bitrix\DB\ORM\Index\IndexInterface`.

```php
public static function getIndexConfigurationCollection(): ConfigurationCollection
{
    return new ConfigurationCollection(static::class)
        ->add(['ENTITY_TYPE'])
        ->add(['ENTITY_TYPE', 'SECTION_ID']);
}
```

Порядок полей в индексе **важен**.

Создать индекс (вызывается в createDbTable()):

```php
$entity = SampleTable::getEntity();
$entity->createAdditionalIndexes();
```

### Связи

Связи формируются после создания таблиц. Удаляются перед удалением таблиц.

Связи вычисляются автоматически относительно полей типа `\Bitrix\Main\ORM\Fields\Relations\Reference`.

Для создания связей выполнить:

```php
$entity = SampleTable::getEntity();
$entity->createForeignKeys();
```

Для удаления связей выполнить:

```php
$entity = SampleTable::getEntity();
$entity->dropForeignKeys();
```

### Ограничения

В классе, описывающем таблицу (наследник `Kosmosafive\Bitrix\DB\ORM\Data\DataManager`),
необходимо реализовать интерфейс `Kosmosafive\Bitrix\DB\ORM\Constraint\ConstraintableInterface`.

```php
public static function getConstraintCollection(): ConstraintCollection
{
    return new ConstraintCollection()
        ->add(new Unique('user_entity', 'USER_ID', 'ENTITY_ID'));
}
```

Создать ограничения (вызывается в createDbTable()):

```php
$entity = SampleTable::getEntity();
$entity->createConstraints();
```

#### Check

`Kosmosafive\Bitrix\DB\ORM\Constraint\Check`

Проверка переданного условия.

```php
new Chech(
    'price',
    'PRICE > 0'
);
```

#### Unique

`Kosmosafive\Bitrix\DB\ORM\Constraint\Unique`

Проверка уникальности одного или нескольких полей.

```php
new Unique(
    'user_entity',
    'USER_ID',
    'ENTITY_ID'
);
```

### Поля

Для маппинга кастомных полей в классе, описывающем таблицу (наследник `Kosmosafive\Bitrix\DB\ORM\Data\DataManager`), 
необходимо модифицировать метод `getMap()`.

```php
public static function getMap(): array
{
    $map = [...];
    
    static::addTableMapping($map);

    return $map;
}
```

#### Boolean

`Kosmosafive\Bitrix\DB\ORM\Fields\BooleanField`

Поле, хранящее булево значение. Без возможности указания замещающего значения для true \ false.

#### Char

`Kosmosafive\Bitrix\DB\ORM\Fields\CharField`

Строка с фиксированной длиной.

#### Datetime

`Kosmosafive\Bitrix\DB\ORM\Fields\DatetimeField`

Поле, хранящее дату и время. Может хранить миллисекунды (size = 6).

#### Unsigned Integer

`Kosmosafive\Bitrix\DB\ORM\Fields\UnsignedIntegerField`

Беззнаковое целое.

#### Uuid

`Kosmosafive\Bitrix\DB\ORM\Fields\UuidField`

Поле, хранящее uuid. Можно использовать в качестве идентификатора.

Перед фильтрацией по значению поля необходимо подготовить данные:

```php
use Ramsey\Uuid\Uuid;

$uuid = Uuid::fromString($value)->getBytes();

SampleTable::query()
    ->where('ID', $uuid);
```

## Миграция

* [Миграция с 1.x на 2.0](doc/migration/2.0.md)