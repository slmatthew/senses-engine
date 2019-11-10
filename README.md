# Senses Engine
**Senses Engine** — движок для ботов ВКонтакте.

## Содержание
* [Начало](https://github.com/slmatthew/senses-engine#senses-engine)
* [Инициализация](https://github.com/slmatthew/senses-engine#инициализация)
* [Класс BotEngine](класс-botengine)
    * [addCommand и addCommands](https://github.com/slmatthew/senses-engine#addcommand--addcommands)
    * [runCommand](https://github.com/slmatthew/senses-engine#runcommand)
    * [addDataHandler](https://github.com/slmatthew/senses-engine#adddatahandler)
* [Класс DataHandler](https://github.com/slmatthew/senses-engine#класс-datahandler)
    * [Конструктор](https://github.com/slmatthew/senses-engine#конструктор)
    * [Получение данных от Callback API](https://github.com/slmatthew/senses-engine#получение-данных-от-callback-api)
    * [Исключения](https://github.com/slmatthew/senses-engine#исключения)
* [Модуль Requests](https://github.com/slmatthew/senses-engine#requests)
    * [request](https://github.com/slmatthew/senses-engine#request)
    * [call](https://github.com/slmatthew/senses-engine#call)
* [Конфигурация](https://github.com/slmatthew/senses-engine#конфигурация)
* [Исключения](https://github.com/slmatthew/senses-engine#исключения-1)
* [Некоторые нюансы](https://github.com/slmatthew/senses-engine#некоторые-нюансы)

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

Рассмотрим основные методы.

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

### runCommand
Проверяет, существует ли команда, и запускает её.

| Параметр | Тип    | Описание                                                                                                                                           |
| ---------|--------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| $name    | string | Название команды. Лучше создавать команды по первому слову, поэтому рекомендуется передавать `explode(' ', $data['object']['message']['text'])[0]` |
| $data    | array  | Данные, полученные от CB или от LP                                                                                                                 |

```php
$BotEngine->runCommand('test', $dataFromCB);
```

### addDataHandler
Добавление обработчика для событий, отличных от `message_new`.

| Параметр | Тип      | Описание                                                |
| ---------|----------|---------------------------------------------------------|
| $name    | string   | Название события                                        |
| $handler | callable | Функция-обработчик. Будет вызвана при получении события |

```php
$BotEngine->addDataHandler('group_leave', function($data) {
	echo '-1 subscriber :(';
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

### Получение данных от Callback API
Начиная с версии 0.2-alpha, весь процесс получения данных автоматизирован. Вам достаточно создать экземпляр класса DataHandler с типом `cb`, и всё должно работать.
```php
$dh = new DataHandler('cb', $be);
```

> Эта функция нуждается в тестировании.

### Исключения
Исключение будет выброшено если:
* будет передан неверный тип получения данных. Исключение с текстом *Unknown type for DataHandler*
* не получится получить новые данные для LP. Исключение с текстом *Can't to get new Longpoll data*

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

## Конфигурация
В репозитории есть файл `config.php.example`. Это — пример конфига. На его основе вы можете создать свой конфиг, в котором обязательно должны быть следующие поля:

| Поле     | Тип    | Описание                            |
|----------|--------|-------------------------------------|
| token    | string | access_token из настроек сообщества |
| group_id | int    | ID сообщества (положительное число) |
| version  | string | Версия VK API                       |

Файл конфига обязательно должен называться `config.php`, а переменная с конфигом — `$config`.

```php
$config = [
	'token' => '',
	'group_id' => 1,
	'version' => '5.103'
];
```

## Исключения
Исключения во всей библиотеке могут быть выброшены если:
* не будет задан конфиг. Исключение с текстом *You need to set config*
* не будет функции request (т.е. не будет подключен модуль Requests). Исключение с текстом *Requests module is not loaded*

## Некоторые нюансы
Пока что библиотека не умеет самостоятельно обрабатывать CB-запросы, но скоро эта возможность появится.
Библиотеку можно использовать только на новых версиях PHP, где появились анонимные функции.
Библиотека настроена для версии API 5.103 и выше.