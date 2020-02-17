# Execute
Класс для работы с методом `execute`.

## Конструктор
Принимает единственный параметр `array $methods`. Он представляет собой массив массивов, которые должны иметь такую структуру:

```php
['method.name', ['name' => 'value']]
```

Например:

```php
$execute = new Execute([
	['messages.send', ['peer_id' => 1, 'message' => 'Hello!', 'random_id' => 0]]
]);

// или

$vk->bot->onCommands(['default'], function($data, $msg) {
	$execute = new Execute([
		$msg->reply('Привет!', [], true),
		$msg->reply('Это сообщение отправлено через execute', [], true),
		$msg->reply('И это тоже', [], true)
	]);
});
```

> Документация по методу `reply` класса `Message` доступна [здесь](message.md#reply)

## getCode
Возвращает код в формате VKScript.

```php
$code = $execute->getCode();
call('execute', ['code' => $code]);
```

## exec
Выполняет запрос к методу execute. Вы можете указать, совершить запрос от имени VK для Android или нет.

```php
$execute->exec();
$execute->exec(true); // от имени VK для Android
```