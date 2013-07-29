# PHP Dependency Injection Container

YetAnother DI — гибкий DI-контейнер, создающий экземпляры классов и их зависимости на основании параметров конструктора класса.
Вам больше не потребуются конфиги, описания зависимостей, именование сервисов, или анонимные функции — всю информацию о связях контейнер будет брать из конструкторов.
В отличии от популярных DI-контейнеров, данный контейнер работает только c Singletone'ами, т.е. с объектами, которые создаются 1 раз.

## Установка

Рекомендуемая установка через [composer](http://getcomposer.org):
```JSON
{
    "require": {
        "yetanother/di": "dev-master"
    }
}
```

## Создание контейнера:
```php
use YetAnother\DI\Container;

$container = new Container();
```

## Создание объектов

```php
$myObject = $container->get('MyClass');
// или
$myObject = $container['MyClass'];
```

## Получение объекта

При создании объекта, контейнер автоматически его сохраняет. Получение уже созданного объекта выполняется той же фунцией:
```php
$myObject = $container->get('MyClass');
// или
$myObject = $container['MyClass'];
```

## Проверка существования

```php
$container->has('MyClass'); // true/false
// или
isset($container['MyClass']);
```

## Ручное добавление объектов

```php
$myObject = new MyClass();
$container->push($myObject);
// или
$container[] = $myObject;

// после добавления можно получить доступ к объекту по имени класса:
$container->get('MyClass') === $myObject; // true
```

## Удаление объектов из контейнера

```php
$container->remove('MyClass');
// или
unset($container['MyClass']);
```

## Создание объектов с зависимостями

Допустим, класс MyModel зависит от класса Database, и эта зависимость описана в конструкторе:
```php
class MyModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
        echo 'MyModel created';
    }
}
class Database()
{
    public function __construct()
    {
        echo 'Database created';
    }
}
```

Тогда, при создании объекта класса MyModel, сначала будет создан объект Database:
```php
$myModel = $container->get('MyModel');
// Database created
// MyModel created
```

Но, если объект класа Database уже создан и хранится в контейнере, то он будет передан в конструктор MyModel, т.е. второй раз создаваться уже не будет:
```php
$db = $container->get('Database');
// Database created
$myModel = $container->get('MyModel');
// MyModel created
```

Таким образом, создаются все зависимости по цепочке.

## Добавление функционала при создании объектов

Если вам необходимы дополнительные действия при создании объектов, можно описать их в анонимной функции:
```php
$container->set('Database', function () {
    $db = new Database();
    $db->connect();
    return $db;
});
// или
$container['Database'] = function () {...};
```

Для того чтобы использовать объекты в анонимной функции, передайте их в качестве параметров функции:
```php
$container->set('UserModel', function (Database $db, Session $session) {
    ...
});
```

## Зависимости от контейнера

Наряду с любыми объектами, вы можете использовать сам контейнер в качестве зависимостей, но это делать не рекомендуется, т.к. при таком подходе сложно отследить связи между классами, а также усложняется процесс тестирования.
```php
class MyClass
{
    public function __construct(Container $container)
    {
        ...
    }
}
```

```php
$container->set('MyClass', function (Container $container) {
    ...
});
```
