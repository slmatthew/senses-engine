<p align="center">
	<a href="https://slmatthew.dev/project/senses" target="_blank"><img alt="Senses Engine logo" title="Senses Engine logo" src="https://repository-images.githubusercontent.com/220678708/660ed700-4127-11ea-9937-59c3788d6295"/></a>
</p>

<p align="center">
	<a href="https://php.net" target="_blank"><img src="https://img.shields.io/badge/php-%3E%3D7.0-blue" alt="PHP version" /></a>
	<a href="https://vk.com/dev/versions" target="_blank"><img src="https://img.shields.io/badge/VK%20API-%3E%3D5.103-lightgrey" alt="VK API version" /></a>
	<a href="https://github.com/slmatthew/senses-engine/releases/latest" target="_blank"><img src="https://img.shields.io/github/v/release/slmatthew/senses-engine.svg?color=red" alt="Latest Stable Version" /></a>
	<a href="https://github.com/slmatthew/senses-engine/actions" target="_blank"><img src="https://github.com/slmatthew/senses-engine/workflows/Create%20ZIP%20files/badge.svg" alt="ZIP Files status" /></a>
	<a href="https://github.com/slmatthew/senses-engine/commits/master" target="_blank"><img src="https://img.shields.io/github/last-commit/slmatthew/senses-engine" alt="Latest commit" /></a>
	<a href="https://github.com/slmatthew/senses-engine/blob/master/LICENSE" target="_blank"><img src="https://img.shields.io/github/license/slmatthew/senses-engine" alt="License" /></a>
</p>

# Senses Engine
**Senses Engine** — библиотека для создания ботов ВКонтакте.

| 📖 [Documentation](docs/) | 🤖 [Examples](docs/examples/) |
|---------------------------|--------------------------------|

## Оглавление
* [Начало](#senses-engine)
* [Обзор](#present)
* [Об этой ветке](#php74)
* [Roadmap](#rmap)
* [Новые версии](#latest)

<a name="present"></a>
## Обзор
Представьте, что вам необходимо создать бота ВКонтакте, получающего данные с помощью Longpoll. Вы пишете функцию для работы с VK API, паралелльно реализовывая цикличные запросы к LP-серверу через `while`.

Теперь посмотрите сюда.
```php
include './loader.php';

$vk = new vk([
  'token' => 'qwerty12345',
  'type' => 'lp'
]);

$vk->bot->onCommands(['test'], function($data, $msg) {
  $msg->reply('Ответ на тестовую команду');
});

$vk->listen();
```

Всё стало гораздо проще. Весь код для работы с VK API скрыт внутри функций библиотеки, вам остаётся лишь добавлять команды и модифицировать классы под себя.

<a name="php74"></a>
## Об этой ветке
В этой ветке находятся файлы из [dev-ветки](https://github.com/slmatthew/senses-engine/tree/dev), которые используют фичи из PHP 7.4. Пожалуйста, не используйте эту версию, если ваша версия PHP меньше 7.4.

<a name="rmap"></a>
## Roadmap
План работы доступен [на вкладке Projects](https://github.com/slmatthew/senses-engine/projects/1).

<a name="latest"></a>
## Новые версии
Самый последний функционал находится в ветке [dev](https://github.com/slmatthew/senses-engine/tree/dev). Обратите внимание: код в данной ветке может быть нестабилен.

Вы можете скачать последний бета-релиз [здесь](https://github.com/slmatthew/senses-engine/releases).