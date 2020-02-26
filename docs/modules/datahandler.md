# DataHandler
Класс, который реализует логику получения данных.

## Исключения
* [TypeException](exceptions.md#type)
* [LongpollException](exceptions.md#lp)

## Логирование
В `loader.php` есть константа `NEED_LP_LOGS`. Если вы установите ее значение на `true`, в консоли будут появляться _(не очень информативные)_ логи работы LP.

## Конструктор
Обрабатывает данные от Callback API или начинает делать запросы к LP-серверу.

| Параметр        | Тип       | Описание                                                   |
|-----------------|-----------|------------------------------------------------------------|
| $type           | string    | Как получать события: `lp` или `cb` (только для сообществ) |
| $be             | BotEngine | Экземпляр класса [BotEngine](botengine.md)                 |
| $confirm_string | string    | Строка для подтверждения Callback API сервера              |

```php
/* First way */
$vk = new vk('lp');
$dh = $vk->client;

/* Second way */
$dh = new DataHandler();
```