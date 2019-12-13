# Модуль Template
Модуль позволяет работать [с шаблонами сообщений](https://vk.com/wall-1_393923).

## Конструктор
| Параметр | Тип    | Описание                                                                         |
|----------|--------|----------------------------------------------------------------------------------|
| $type    | string | Тип шаблона. Сейчас доступен только `carousel`. Этот тип установлен по умолчанию |

```php
$template = new Template();
```

## addCarouselElement
Метод для добавления элемента в шаблон.

| Параметр     | Тип    | Описание                                                                                           |
|--------------|--------|----------------------------------------------------------------------------------------------------|
| $title       | string | Название элемента                                                                                  |
| $description | string | Описание элемента                                                                                  |
| $photo_id    | string | ID фотографии                                                                                      |
| $buttons     | array  | Массив кнопок. Может быть получен через [TemplateButtons](#генерация-кнопок-через-templatebuttons) |
| $action      | array  | Действие при клике на элемент                                                                      |

```php
$template->addCarouselElement('Title', 'Description', '', $buttons, [
  'type' => 'open_link',
  'link' => 'https://slmatthew.ru'
]);
```

> Полная документация: [click](https://vk.cc/a8hzdu)

## get
Получить шаблон.

| Параметр | Тип  | Описание                                                                              |
|----------|------|---------------------------------------------------------------------------------------|
| $json    | bool | `true` — метод вернет JSON-строку, `false` — метод вернет массив. По умолчанию `true` |

```php
call('messages.send', [
  'peer_id' => 1,
  'random_id' => 0,
  'message' => 'Hello!',
  'template' => $template->get()
]);
```

## Генерация кнопок через TemplateButtons
Вы можете использовать класс TemplateButtons для генерации кнопок. Методы [addButton](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md#addbutton), [addTextButton](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md#другой-способ-добавления-кнопок), [addLocationButton](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md#другой-способ-добавления-кнопок), [addPayButton](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md#другой-способ-добавления-кнопок) и [addAppButton](https://github.com/slmatthew/senses-engine/blob/master/docs/keyboard.md#другой-способ-добавления-кнопок) идентичны в параметрах соответствующим методам из класса Keyboard. Метод `get()` возвращает массив кнопок. Конструктор возвращает класс TemplateButtons.