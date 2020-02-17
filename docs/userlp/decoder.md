# LpDecoder
Декодер событий User LP, основанный на [неофициальной документации](https://github.com/danyadev/longpoll-doc).

## Конструктор
Принимает единственный параметр `array $event`, содержащий событие из User LP.

```php
$be->on('80', function($data) {
  $decoder = new LpDecoder($data);
  // Дальнейший код будет продолжением этого
});
```

## decode
Собственно, сам декодер. Не имеет параметров. Возвращает `false`, если была ошибка при создании экземпляра класса, либо ассоциативный массив.

Успешность декодирования вы можете отследить по полю `decoded`. Если оно равно `false`, в декодере нет информации об этом событии и оно вернулось в поле `event`. В ином случае всегда возвращается поле `event_id` и дополнительные поля декодированного события.

```php
$decoded = $decoder->decode();
if($decoded === false) {
	echo "Критическая ошибка";
} elseif($decoded['decoded']) {
	echo implode("\n", [
		"ID события: {$decoded['event_id']}",
		"Всего диалогов: {$decoded['count']}",
		"С уведомлениями: {$decoded['count_with_notifications']}"
	]);
} else {
	echo "Не удалось декодировать событие";
}
```

### Поддерживаемые события
В этом списке представлены поля, которые возвращаются в результате обработки определенных событий.

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