<p align="center">
  <img alt="Senses Engine logo" src="https://static.slmatthew.ru/senses/image.png"/>
</p>

<p align="center">
	<img src="https://img.shields.io/badge/php-%3E%3D7.0-red" alt="PHP version" />
	<img src="https://img.shields.io/badge/VK%20API-%3E%3D5.103-lightgrey" alt="VK API version" />
	<img src="https://img.shields.io/github/v/release/slmatthew/senses-engine.svg?color=red" alt="Latest Stable Version" />
	<img src="https://img.shields.io/github/last-commit/slmatthew/senses-engine" alt="Latest commit" />
	<img src="https://img.shields.io/github/license/slmatthew/senses-engine" alt="License" />
</p>

# Senses Engine
**Senses Engine** — движок для ботов ВКонтакте.

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
	- [LP Decoder](https://github.com/slmatthew/senses-engine/blob/master/docs/lpdecoder.md)
* [Audio](https://github.com/slmatthew/senses-engine/blob/master/docs/audio.md)
* [Конфигурация](https://github.com/slmatthew/senses-engine/blob/master/docs/config.md)
* [Исключения](#исключения)
* [Некоторые нюансы](#некоторые-нюансы)
* [Roadmap](#roadmap)

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
* не будет задан конфиг. `ConfigException` с текстом *You need to set config*
* не будет функции request (т.е. не будет подключен модуль Requests). `RequestsException` с текстом *Requests module is not loaded*

## Некоторые нюансы
Библиотеку можно использовать только на новых версиях PHP, где появились анонимные функции.
Библиотека настроена для версии API 5.103 и выше.

## Roadmap
- [x] Перевод событий User LP в нормальный вид
- [x] Работа с audio.*
- [ ] Мультиаккаунт
- [ ] Работа с авторизацией
- [ ] Execute
- [ ] Удобная работа с вложениями