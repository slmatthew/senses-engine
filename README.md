# Senses Engine
**Senses Engine** — движок для ботов ВКонтакте.

## Инициализация
Ниже показано, как правильно подключать движок к боту:
```php
include '../loader.php';

$be = new BotEngine();

$be->addCommand('привет', function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Привет!', 'random_id' => 0]);
  return true;
});

$be->addCommands(['Команда', 'Раз', 'Два', 'Три'], function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Вот такие вот дела', 'random_id' => 0]);
  return true;
});

$dh = new DataHandler('lp', $be);
```

## Класс BotEngine
Список паблик методов класса BotEngine:
```php
public function addCommand(string $name, callable $handler) {}; // добавление команды
public function addCommands(array $names, callable $handler) {}; // добавление нескольких команд с одним обработчиком
public function runCommand(string $name, array $data) {}; // запуск обработчика команды если он указан
public function onData(array $data) {}; // обработчик новых событий
public function addDataHandler(string $name, callable $handler) {}; // добавление собственных обработчиков событий (кроме message_new)
```

Рассмотрим два основных метода.

### addCommand & addCommands
Эти методы используются для добавления новых команд. addCommand — для одной команды, addCommands — для нескольких команд с одним обработчиком.

| Параметр       | Тип            | Описание                                                                                      |
| ---------------|----------------|-----------------------------------------------------------------------------------------------|
| $name / $names | string / array | Название команды или массив с названиями команд (для addCommand и addCommands соответственно) |
| $handler       | callable       | Функция-обработчик. Будет вызвана при получении команды                                       |

```php
$BotEngine->addCommand('привет', function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Привет!', 'random_id' => 0]);
  return true;
});

$BotEngine->addCommands(['Команда', 'Раз', 'Два', 'Три'], function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Вот такие вот дела', 'random_id' => 0]);
  return true;
});
```

Функция-обработчик всегда должна возвращать `true`, иначе будет запущен дефолтный обработчик. Его также можно изменить:
```php
$BotEngine->addCommand('default', function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Я не знаю, как на это ответить', 'random_id' => 0]);
  return true;
});
```

## Класс DataHandler
Экземпляр DataHandler необходимо создавать **после** BotEngine, т.к. последний необходим для обработки данных. Структура должна выглядеть так:
```php
$be = new BotEngine();

// commands

$dh = new DataHandler('lp', $be);
```

### Конструктор
Первый аргумент — тип получения событий в сообществе: `cb` — Callback API, `lp` — LongPoll API. Второй — экземпляр класса BotEngine, в метод которого будет передано полученное событие.
```php
$dh = new DataHandler('lp', $be);
```

### onData
Если вы используете CB, то вам необходимо вручную вызывать метод onData при получении события. Первый аргумент — JSON события, полученный так: `json_decode(file_get_contents('php://input'), true)`. Второй — экземпляр класса BotEngine.
```php
$dh = new DataHandler('cb', $be);

$data = file_get_contents('php://input');
if(!is_null($data)) {
  $data = json_decode($data, true); // можно еще добавить свои проверки
  $dh->onData($data, $be);
}
```
> Передавать экземпляр BotEngine нужно из-за того, что при использовании LP библиотека сама инициализирует поллинг и сама вызывает метод onData, передавая ему BotEngine из конструктора. Обработка CB запросов целиком ложится на разработчика, и библиотека в этом никак не участвует.

## Requests
В библиотеке существует вспомогательный модуль Requests, предназначенный (удивительно) для запросов к VK API. Это не класс, а просто две функции, которые вы можете использовать в своём коде.

### request
Функция, предназначенная для запросов к API. API всегда должно возвращать валидный JSON, или будет ошибка. Функция возвращает массив, полученный в результате `json_decode($data, true)`.

| Параметр    | Тип    | Описание                                                    |
| ------------|--------|-------------------------------------------------------------|
| $url        | string | URL запроса                                                 |
| $postfields | array  | Данные запроса вида `['name' => 'value']`                   |
| $agent      | string | User Agent, по умолчанию: `Senses Bot Engine/%версия либы%` |

```php
$json = request('https://api.vk.com/method/users.get', ['user_ids' => '1']);
echo $json['response'][0]['first_name'];
```

### call
Функция используется для запросов к VK API. Возвращает JSON с результатом запроса.

| Параметр | Тип    | Описание                                                                                       |
|----------|--------|------------------------------------------------------------------------------------------------|
| $m       | string | Название метода VK API                                                                         |
| $p       | array  | Параметры вида `['name' => 'value']`                                                           |
| $o       | bool   | Сделать запрос под видом официального клиента (`true`) или нет (`false`), по умолчанию `false` |

Есть некоторые настройки в параметре `$p`.

| Настройка    | Описание                                                                                                             |
|--------------|----------------------------------------------------------------------------------------------------------------------|
| access_token | Используется для передачи кастомного токена, а не указанного в `config.php`                                          |
| v            | Используется для передачи кастомной версии API, а не указанной в `config.php`. Должна быть строкой                   |
| unsetToken   | Используется, если по какой-то причине нужно совершить запрос без токена. Например, для некоторых методов `secure.*` |

```php
$json = call('messages.send', ['peer_id' => 1, 'message' => 'Hello!', 'random_id' => 0]);
echo $json['response'];
```