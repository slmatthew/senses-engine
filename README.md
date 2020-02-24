<p align="center">
	<img alt="Senses Engine logo" title="Senses Engine logo" src="https://repository-images.githubusercontent.com/220678708/660ed700-4127-11ea-9937-59c3788d6295"/>
</p>

<p align="center">
	<img src="https://img.shields.io/badge/php-%3E%3D7.0-blue" alt="PHP version" />
	<img src="https://img.shields.io/badge/VK%20API-%3E%3D5.103-lightgrey" alt="VK API version" />
	<img src="https://img.shields.io/github/v/release/slmatthew/senses-engine.svg?color=red" alt="Latest Stable Version" />
	<img src="https://github.com/slmatthew/senses-engine/workflows/Create%20ZIP%20files/badge.svg" alt="ZIP Files status" />
	<img src="https://img.shields.io/github/last-commit/slmatthew/senses-engine" alt="Latest commit" />
	<img src="https://img.shields.io/github/license/slmatthew/senses-engine" alt="License" />
</p>

# Senses Engine
**Senses Engine** — библиотека для создания ботов ВКонтакте.

[Документация](https://github.com/slmatthew/senses-engine/tree/master/docs)

## Оглавление
* [Начало](#senses-engine)
* [Обзор](#present)
	- [Старый способ подключения](#old-way)
* [Roadmap](#rmap)

<a name="present"></a>
## Обзор
Представьте, что вам необходимо создать бота ВКонтакте, получающего данные с помощью Longpoll. Вы пишете функцию для работы с VK API, паралелльно реализовывая цикличные запросы к LP-серверу через `while`.

Теперь посмотрите сюда.
```php
include './loader.php';

$vk = new vk('lp');

$vk->bot->onCommands(['test'], function($data, $msg) {
  $msg->reply('Ответ на тестовую команду');
});

$vk->listen();
```

Всё стало гораздо проще. Весь код для работы с VK API скрыт внутри функций библиотеки, вам остаётся лишь добавлять команды и модифицировать классы под себя.

<a name="old-way"></a>
### Старый способ подключения
До версии `0.8` использовался другой способ создания ботов. Он используется под капотом нового класса `vk`. Рекомендуется использовать новый способ.

```php
include './loader.php';

$be = new BotEngine();

$be->onCommands(['test', 'тест', 'тестирование'], function($data, $msg) {
  $msg->reply('Ответ на тестовую команду');
});

$dh = new DataHandler('lp', $be);
```

<a name="rmap"></a>
## Roadmap
- [x] Перевод событий User LP в нормальный вид
- [x] Работа с audio.*
- [x] Execute
- [x] Работа с авторизацией
- [ ] Мультиаккаунт
- [ ] Удобная работа с вложениями