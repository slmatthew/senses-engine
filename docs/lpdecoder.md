# Longpoll decoder
Декодер событий User LP.

## Конструктор
| Параметр | Тип   | Описание           |
| ---------|-------|--------------------|
| $event   | array | Событие из User LP |

```php
// Мы уже получили событие Longpoll. Например, через $botEngine->addDataHandler()

$decoder = new LpDecoder($event);
```

## decode
Декодер. Аргументов нет.

```php
$decoded = $decoder->decode();
```

### Формат ответа
Если произошла какая-то ошибка, вернётся `false`.

При успешном декодировании, вернётся ассоциативный массив. Главное поле в нём - это `decoded` (`bool`). Если он равен `true`, то всё прошло успешно. Если `false`, то это событие не поддерживается и было возвращено в поле `event`.

Ниже представлено описание полей, которые возвращаются при обработке определённых событий. Более подробная информация есть [здесь](https://github.com/danyadev/longpoll-doc).

* Событие 2
	- `peer_id`, `msg_id`, `flags`
* События 4, 5, 18
	- `msg_id`, `flags`, `peer_id`, `timestamp`, `text`, `random_id`, `conversation_msg_id`, `edit_time`, `attachs`, `action?`, другие поля
* События 6, 7
	- `peer_id`, `msg_id`, `count`
* Событие 8
	- `user_id` (положительное число), `platform`, `timestamp`, `app_id`
* Событие 9
	- `user_id` (положительное число), `isTimeout`, `timestamp`, `app_id`
* События 10, 12
	- `peer_id`, `flags`
* Событие 13
	- `peer_id`, `last_msg_id`
* Событие 19
	- `msg_id`
* Событие 51
	- `chat_id`
* Событие 52
	- `peer_id`, `type`, `extra`
* События 63, 64
	- `peer_id`, `from_ids`, `from_ids_count`, `timestamp`
* Событие 80
	- `count`, `count_with_notifications`
* Событие 81
	- `user_id` (положительное число), `state`, `timestamp`
* Событие 114
	- `peer_id`, `sound`, `disabled_until`

### Пример
```php
// Мы получили 80 событие, например
$decoded = $decoder->decode();
echo implode("\n", [
	"ID события: {$decoded['event_id']}",
	"Всего диалогов: {$decoded['count']}",
	"С уведомлениями: {$decoded['count_with_notifications']}"
]);
```