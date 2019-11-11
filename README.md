# Senses Engine
**Senses Engine** — движок для ботов ВКонтакте.

## Содержание
* [Начало](https://github.com/slmatthew/senses-engine#senses-engine)
* [Инициализация](https://github.com/slmatthew/senses-engine#инициализация)
* [Класс BotEngine](класс-botengine)
    * [addCommand и addCommands](https://github.com/slmatthew/senses-engine#addcommand--addcommands)
    * [runCommand](https://github.com/slmatthew/senses-engine#runcommand)
    * [addDataHandler](https://github.com/slmatthew/senses-engine#adddatahandler)
    * [addPayloadCommands](https://github.com/slmatthew/senses-engine#addpayloadcommands)
    * [addCommandsAlias](https://github.com/slmatthew/senses-engine#addcommandsalias)
    * [checkAllCommands](https://github.com/slmatthew/senses-engine#checkallcommands)
* [Класс DataHandler](https://github.com/slmatthew/senses-engine#класс-datahandler)
    * [Конструктор](https://github.com/slmatthew/senses-engine#конструктор)
    * [Получение данных от Callback API](https://github.com/slmatthew/senses-engine#получение-данных-от-callback-api)
    * [Исключения](https://github.com/slmatthew/senses-engine#исключения)
    * [Логирование действий LongPoll API](https://github.com/slmatthew/senses-engine#логирование-действий-longpoll-api)
* [Модуль Requests](https://github.com/slmatthew/senses-engine#requests)
    * [request](https://github.com/slmatthew/senses-engine#request)
    * [call](https://github.com/slmatthew/senses-engine#call)
* [Модуль Keyboard](https://github.com/slmatthew/senses-engine#модуль-keyboard)
    * [Конструктор](https://github.com/slmatthew/senses-engine#конструктор-1)
    * [addButton](https://github.com/slmatthew/senses-engine#addbutton)
    * [addLine](https://github.com/slmatthew/senses-engine#addline)
    * [getKeyboard](https://github.com/slmatthew/senses-engine#getkeyboard)
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
Сердце движка. Рассмотрим основные методы.

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

### addPayloadCommands
Добавление команды, которая будет определяться по значению поля command в payload.

| Параметр | Тип      | Описание                                                    |
| ---------|----------|-------------------------------------------------------------|
| $names   | string   | Названия команд. Можно передать одно значение или несколько |
| $handler | callable | Функция-обработчик                                          |

```php
$BotEngine->addPayloadCommands(['start'], function($data) {
  // Ответ на нажатие кнопки Начать
});

$BotEngine->addPayloadCommands(['main', 'menu'], function($data) {
  // Например, открыть клавиатуру с меню
});
```

> Эта функция добавляет команду, которая будет реагировать на значение поля `command` в `payload` сообщения, т.е. `json_decode($data['object']['message']['payload'], true)['command']`. Следовательно, вы должны использовать слово `command` в качестве имени поля, т.е. JSON должен иметь вид `{"command": %название%}`

### addCommandsAlias
Добавляет алиас для payload-команды.

| Параметр     | Тип    | Описание                                                             |
| -------------|--------|----------------------------------------------------------------------|
| $payloadName | string | Название payload-команды                                             |
| $textName    | string | Название текстовой команды (которая определется по тексту сообщения) |

```php
$be->addCommandsAlias('start', 'меню'); // Нажатие кнопки с payload = {"command": "start"} будет эквивалентно сообщению с text = меню
```

### checkAllCommands
Проверяет, есть ли команда какого-либо типа. При этом приоритет имеют payload-команды.

| Параметр     | Тип    | Описание                   |
| -------------|--------|----------------------------|
| $payloadName | string | Название payload-команды   |
| $textName    | string | Название текстовой команды |
| $data        | array  | Сообщение                  |

```php
$BotEngine->checkAllCommands('start', 'меню', $data);
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

### Исключения
Исключение будет выброшено если:
* будет передан неверный тип получения данных. Исключение с текстом *Unknown type for DataHandler*
* не получится получить новые данные для LP. Исключение с текстом *Can't to get new Longpoll data*

### Логирование действий LongPoll API
Вы можете включать и выключать базовое логирование событий LongPoll API. Чтобы включить логирование, измените значение константы `NEED_LP_LOGS` в файле `loader.php` на `true`, а чтобы выключить — `false`.

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

## Модуль Keyboard
Модуль используется для генерации клавиатур.

### Конструктор
| Параметр  | Тип  | Описание                                           |
|-----------|------|----------------------------------------------------|
| $one_time | bool | Скрывать ли клавиатуру после первого использования |
| $inline   | bool | Должна ли клавиатура отображаться внутри сообщения |

```php
$fkb = new Keyboard(true, false);
$skb = new Keyboard(false, true); // при $inline = true $one_time может иметь любое значение
```

### addButton
Метод, используемый для добавления кнопки в строку.

| Параметр  | Тип    | Описание                                                                                                                                              |
|-----------|--------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| $action   | array  | [Документация](https://vk.com/dev/bots_docs_3?f=4.2.%2B%D0%A1%D1%82%D1%80%D1%83%D0%BA%D1%82%D1%83%D1%80%D0%B0%2B%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85) |
| $color    | string | Цвет кнопки. Только для кнопки с `$action['type'] == 'text'`                                                                                          |

```php
$kb->addButton([
  'type' => 'text',
  'label' => 'Текст кнопки',
  'payload' => ['command' => 'start']
], 'positive');
```

> Информация об ограничениях есть в документации.

> В `$action['payload']` вы можете передавать массив, либа сама преобразует его в JSON.

### addLine
Добавление новой строки в клавиатуру.

```php
$kb->addLine();
```

### getKeyboard
Получить построенную клавиатуру. Если в первый (и единственный) параметр передать `true`, вернётся JSON.

```php
$kb->getKeyboard(); // array
$kb->getKeyboard(true); // string (JSON)
```

## Конфигурация
В репозитории есть файл `config.php.example`. Это — пример конфига. На его основе вы можете создать свой конфиг, в котором обязательно должны быть следующие поля:

| Поле     | Тип    | Описание                            |
|----------|--------|-------------------------------------|
| token    | string | access_token из настроек сообщества |
| secret   | string | Секретный ключ или пустая строка    |
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
Библиотеку можно использовать только на новых версиях PHP, где появились анонимные функции.
Библиотека настроена для версии API 5.103 и выше.