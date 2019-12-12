# Senses Engine
**Senses Engine** — движок для ботов ВКонтакте.

[![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/slmatthew/senses-engine?label=Senses%20Engine)](https://github.com/slmatthew/senses-engine/tags)
[![GitHub last commit](https://img.shields.io/github/last-commit/slmatthew/senses-engine)](https://github.com/slmatthew/senses-engine/commits/master)
![GitHub repo size](https://img.shields.io/github/repo-size/slmatthew/senses-engine)

[![slmatthew VK](https://img.shields.io/badge/slmatthew-VK-blue)](https://vk.com/slmatthew)
[![slmatthew TG](https://img.shields.io/badge/slmatthew-TG-blue)](https://t.me/slmatthew)

## Содержание
* [Начало](#senses-engine)
* [Инициализация](#инициализация)
* [Класс BotEngine](#класс-botengine)
    * [addCommand и addCommands](#addcommand--addcommands)
    * [runCommand](#runcommand)
    * [addDataHandler](#adddatahandler)
    * [addPayloadCommands](#addpayloadcommands)
    * [addCommandsAlias](#addcommandsalias)
    * [checkAllCommands](#checkallcommands)
    * [И ещё кое-что](#и-ещё-кое-что)
* [Класс SBSC (Step-by-step commands)](#класс-sbsc-step-by-step-commands)
    * [addSbsCommand](#addsbscommand)
    * [checkSbsCommand](#checksbscommand)
    * [handleSbsCommand](#handlesbscommand)
    * [checkAllCommands](#checkallcommands-1)
* [Класс DataHandler](#класс-datahandler)
    * [Конструктор](#конструктор)
    * [Получение данных от Callback API](#получение-данных-от-callback-api)
    * [Исключения](#исключения)
    * [Логирование действий LongPoll API](#логирование-действий-longpoll-api)
* [Модуль Requests](#requests)
    * [request](#request)
    * [call](#call)
* [Модуль Keyboard](#модуль-keyboard)
    * [Конструктор](#конструктор-1)
    * [inline и oneTime](#inline-и-onetime)
    * [addButton](#addbutton)
    * [Другой способ добавления кнопок](#другой-способ-добавления-кнопок)
    * [addLine](#addline)
    * [get](#get)
* [User Longpoll](#user-longpoll)
* [Конфигурация](#конфигурация)
* [Исключения](#исключения-1)
* [Некоторые нюансы](#некоторые-нюансы)

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

> Вы можете задать режим строгого соблюдения регистра. Для этого достаточно при создании экземпляра класса BotEngine передать единственным аргументом в конструктор `false`.

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

### И ещё кое-что
С версии `0.5.1` вы можете самостоятельно отключать обработку команд в определенных случаях. [Добавьте DataHandler](#adddatahandler) для события `message_new`, функция которого будет возвращать `false`. В таком случае обработка команд будет пропущена.

```php
$be->addDataHandler('message_new', function($data) {
  if($data['object']['message']['peer_id'] == 1) {
    // Это же Дуров!

    return false;
  }

  // Это не Дуров, проверяем команды

  return true;
});
```

## Класс SBSC (Step-by-step commands)
Класс, основанный на [BotEngine](#класс-botengine) и позволяющий создать команды с поэтапным вводом данных (step-by-step commands). Вместо BotEngine вы можете создавать экземпляр класса SBSCommands:
```php
$be = new SBSCommands();

// commands

$dh = new DataHandler('lp', $be);
```

> Если вам не нужно создавать команды с поэтапным вводом данных, такие как оформление заказа и прочие, используйте класс BotEngine.

### addSbsCommand
Добавление sbs-команды.

| Параметр  | Тип      | Описание                                                                                  |
| ----------|----------|-------------------------------------------------------------------------------------------|
| $type     | string   | Тип команды: `payload` (обработка поля *payload*) или `text` (обработка текстовых команд) |
| $command  | string   | Команда, например *заказ*                                                                 |
| $steps    | int      | Количество шагов. Далее будет представлен пример использования этой функции               |
| $handler  | callable | Функция-обработчик. Первый аргумент — информация от движка, второй — информация от VK API |

Информация от движка (массив)

| Параметр  | Тип    | Описание                                       |
| ----------|--------|------------------------------------------------|
| $command  | string | Название команды, например *заказ* или *order* |
| $type     | string | Тип команды: `payload` или `text`              |
| $step     | int    | Текущий «шаг». Минимально — 0                  |
| $from_id  | int    | ID отправителя                                 |

Ваша функция должна возвращать `true`, чтобы пользователь перешел на следующий «шаг» заполнения данных. Если вернется `false`, то движок посчитает, что данные были указаны неверно, и значение `step` не изменится.

```php
$be->addSbsCommand('payload', 'order', 3, function($e, $a) {
  call('messages.send', ['peer_id' => $a['object']['message']['peer_id'], 'message' => "@id{$e['from_id']} находится на ".($e['step'] + 1)." шаге выполнения {$e['type']}-команды «{$e['command']}»", 'random_id' => 0]);
  return true;
});

$be->addSbsCommand('payload', 'handleSteps', 3, function($e, $a) {
  switch($e['step']) {
    case 0:
      if($a['object']['message']['text'] != 'привет') {
        call('messages.send', ['peer_id' => $a['object']['message']['peer_id'], 'message' => 'Неправильно!', 'random_id' => 0]);
        return false;
      }
      // your code
      break;

    // ...
  }

  return true;
});
```

### checkSbsCommand
Проверка типа команды. Возвращает `false`, если команды не существует. Принимает единственный `string` параметр — имя команды.

```php
if($be->checkSbsCommand('order') == 'payload') echo "order - payload\n";
if($be->checkSbsCommand('заказ') == 'text') echo "заказ - text\n";
if($be->checkSbsCommand('randomWord') === false) echo "randomWord - false\n";
```

### handleSbsCommand
Обработчик sbs-команд. В этой функции заключена логика перехода с одного шага на другой.

| Параметр  | Тип    | Описание                          |
|-----------|--------|-----------------------------------|
| $type     | string | Тип команды: `payload` или `text` |
| $name     | string | Имя команды                       |
| $data     | array  | Данные от VK API                  |

```php
$be->handleSbsCommand('text', 'заказ', $data);
```

> Эта функция используется внутри движка. Вы можете использовать её только в случае, когда нужно, например, заменить одну команду на другую, сохранив функционал.

### checkAllCommands
Слегка измененная функция из оригинального BotEngine, включающая в себя логику обработки sbs-команд.

| Параметр     | Тип    | Описание                   |
|--------------|--------|----------------------------|
| $payloadName | string | Название payload-команды   |
| $textName    | string | Название текстовой команды |
| $data        | array  | Сообщение                  |

[Пример использования](#checkallcommands)

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

### inline и oneTime
Вы можете задать значение `one_time` и `inline` после создания экземпляра класса. Для этого используйте методы `oneTime()` и `inline()`, которые принимают единственный bool-параметр `$enabled`.

```php
$kb->oneTime(false);
$kb->inline(true);
```

### addButton
Метод, используемый для добавления кнопки в строку.

| Параметр  | Тип    | Описание                                                                                                                                              |
|-----------|--------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| $action   | array  | [Документация](https://vk.com/dev/bots_docs_3?f=4.2.%2B%D0%A1%D1%82%D1%80%D1%83%D0%BA%D1%82%D1%83%D1%80%D0%B0%2B%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85) |
| $color    | string | Цвет кнопки. Только для кнопки с `$action['type'] == 'text'`                                                                                          |

Этот метод возвращает класс Keyboard, что означает, что вы можете сразу добавлять новую кнопку. Пример ниже.

```php
$kb->addButton([
  'type' => 'text',
  'label' => 'Текст кнопки',
  'payload' => ['command' => 'start']
], 'positive');

$kb
  ->addButton([
    'type' => 'text',
    'label' => 'Текст кнопки',
    'payload' => ['command' => 'start']
  ], 'positive')
  ->addButton([
    'type' => 'text',
    'label' => 'Текст кнопки',
    'payload' => ['command' => 'start']
  ], 'positive');
```

> Информация об ограничениях есть в документации.

> В `$action['payload']` вы можете передавать массив, либа сама преобразует его в JSON.

### Другой способ добавления кнопок
С версии `0.6` вы можете использовать другой способ добавления кнопок по их типу, а так же использовать константы класса для определения цвета.

| Имя константы    | Значение  |
|------------------|-----------|
| PRIMARY_BUTTON   | primary   |
| SECONDARY_BUTTON | secondary |
| NEGATIVE_BUTTON  | negative  |
| POSITIVE_BUTTON  | positive  |

Ниже приведены все новые функции с параметрами, описанными в [документации](https://vk.com/dev/bots_docs_3). Их можно также добавлять друг за другом (так же, как и с методом `addButton`, пример выше).

```php
$kb->addTextButton(string $label, array $payload = [], string $color = $kb::PRIMARY_BUTTON);
$kb->addLocationButton(array $payload = []);
$kb->addPayButton(array $hash, array $payload = []);
$kb->addAppButton(int $app_id, int $owner_id, string $label, string $hash = '', array $payload = []);
```

> *Обратите внимание:* в функции `addPayButton` параметр `$hash` имеет тип `array` в отличие от документации.

### addLine
Добавление новой строки в клавиатуру.

```php
$kb->addLine();
```

### get
Получить построенную клавиатуру. Если в первый (и единственный) параметр передать `true`, вернётся JSON.

```php
$kb->get(); // array
$kb->get(true); // string (JSON)
```

> До версии `0.6` функция имела название `getKeyboard`.

## User Longpoll
С версии `0.6` движок умеет работать с User Longpoll. В файл с конфигом были внесены изменения: добавлено новое поле `type`, изменено названия поля `group_id` на `api_id`. Информация по конфигу есть в следующем пункте.

Чтобы движок начал работать с User LP, в конфиг-файле нужно указать `type = user`. Далее работа с командами идет как обычно, за исключением того, что пользователи не могут отправлять клавиатуру и обрабатывать payload-команды. Также, в поле `$data` приходит массив [из longpoll](https://vk.com/dev/using_longpoll_2?f=3.%2B%D0%A1%D1%82%D1%80%D1%83%D0%BA%D1%82%D1%83%D1%80%D0%B0%2B%D1%81%D0%BE%D0%B1%D1%8B%D1%82%D0%B8%D0%B9), вам нужно обрабатывать всё вручную. Буду рад pr с модулем, который будет конвертировать данные в нормальный формат.

> Вместо названий событий при указании `datahandler` используйте код события. Например, вместо `$be->addDataHandler('message_new', function() {})` используйте `$be->addDataHandler('4', function() {})`. Первый параметр должен быть строкой.

## Конфигурация
В репозитории есть файл `config.php.example`. Это — пример конфига. На его основе вы можете создать свой конфиг, в котором обязательно должны быть следующие поля:

| Поле     | Тип     | Описание                                                                         |
|----------|---------|--------------------------------------------------------------------------------- |
| type     | string  | `community` или `user`. Влияет на используемый longpoll (`user` или `bots`)      |
| token    | string  | access_token из настроек сообщества                                              |
| secret   | string  | Секретный ключ или пустая строка                                                 |
| api_id   | int     | ID сообщества или пользователя. Всегда положительное число                       |
| version  | string  | Версия VK API                                                                    |
| dev      | mixed[] | Если указан этот параметр, то в модуле Requests `CURLOPT_SSL_VERIFYPEER = false` |

Файл конфига обязательно должен называться `config.php`, а переменная с конфигом — `$config`.

```php
// Для прода
$config = [
  'type' => 'community',
  'token' => 'x6pstvcdeyp5y8c82gthdgc22h7za5aq5pf6cf7su3yf3ur2eassz8uxuxk6q2aacy5m6e5e3kq5eybw3upsk',
  'api_id' => 1,
  'version' => '5.103'
];

// Для локальной разработки
$config = [
  'type' => 'community',
  'token' => 'x6pstvcdeyp5y8c82gthdgc22h7za5aq5pf6cf7su3yf3ur2eassz8uxuxk6q2aacy5m6e5e3kq5eybw3upsk',
  'api_id' => 1,
  'version' => '5.103',
  'dev' => true
];
```

## Исключения
Исключения во всей библиотеке могут быть выброшены если:
* не будет задан конфиг. Исключение с текстом *You need to set config*
* не будет функции request (т.е. не будет подключен модуль Requests). Исключение с текстом *Requests module is not loaded*

## Некоторые нюансы
Библиотеку можно использовать только на новых версиях PHP, где появились анонимные функции.
Библиотека настроена для версии API 5.103 и выше.