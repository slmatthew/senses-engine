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
* [Класс BotEngine](https://github.com/slmatthew/senses-engine/blob/master/docs/botengine.md)
* [Класс SBSC (Step-by-step commands)](https://github.com/slmatthew/senses-engine/blob/master/docs/sbsc.md)
* [Класс DataHandler](https://github.com/slmatthew/senses-engine/blob/master/docs/datahandler.md)
* [Модуль Requests](https://github.com/slmatthew/senses-engine/blob/master/docs/requests.md)
* [Модуль Keyboard](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md)
* [Модуль Template](https://github.com/slmatthew/senses-engine/blob/master/docs/template.md)
* [User Longpoll](https://github.com/slmatthew/senses-engine/blob/master/docs/userlp.md)
* [Конфигурация](https://github.com/slmatthew/senses-engine/blob/master/docs/config.md)
* [Исключения](#исключения)
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

## Исключения
Исключения во всей библиотеке могут быть выброшены если:
* не будет задан конфиг. Исключение с текстом *You need to set config*
* не будет функции request (т.е. не будет подключен модуль Requests). Исключение с текстом *Requests module is not loaded*

## Некоторые нюансы
Библиотеку можно использовать только на новых версиях PHP, где появились анонимные функции.
Библиотека настроена для версии API 5.103 и выше.