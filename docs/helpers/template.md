# Template
Генерирует [шаблоны сообщений](https://vk.com/dev/bot_docs_templates?f=5.%20%D0%A8%D0%B0%D0%B1%D0%BB%D0%BE%D0%BD%D1%8B%20%D1%81%D0%BE%D0%BE%D0%B1%D1%89%D0%B5%D0%BD%D0%B8%D0%B9).

## Конструктор
| Параметр | Тип    | Описание                             |
|----------|--------|--------------------------------------|
| $type    | string | Тип шаблона. По умолчанию `carousel` |

```php
$template = new Template();
```

## addCarouselElement
Добавить элемент в карусель. Доступно только если `type = carousel`, иначе будет сгенерировано [ParameterException](../modules/exceptions.md#param)

| Параметр     | Тип    | Описание                                                      |
|--------------|--------|---------------------------------------------------------------|
| $buttons     | array  | Кнопки                                                        |
| $title       | string | Название карточки. По умолчанию пустая строка                 |
| $description | string | Описание. По умолчанию пустая строка                          |
| $photo_id    | string | ID фотографии. По умолчанию пустая строка                     |
| $action      | array  | Описывает действие при нажатии на карточку. По умолчанию `[]` |

> Более подробная документация: [click](https://vk.com/dev/bot_docs_templates?f=5.1.%20%D0%9A%D0%B0%D1%80%D1%83%D1%81%D0%B5%D0%BB%D0%B8)

### Создание кнопок
Структура кнопок в шаблонах немного отличается от той, которая используется в обычной клавиатуре. Для создания кнопок для шаблонов вы можете использовать класс `TemplateButtons`.

Метода этого класса аналогичны классу [Keyboard](keyboard.md), но есть некоторые отличия:
* отсутствуют свойства `one_time` и `inline`, т.е. нет конструктора и отсутствуют соответствующие методы
* `get` возвращает массив с кнопками, параметров не имеет

```php
$buttons = new TemplateButtons();
$buttons->addTextButton('Carousel button', ['command' => 'test']);

$template->addCarouselElement('Title', $buttons->get(), 'Description', '', ['type' => 'open_link', 'link' => 'https://slmatthew.dev']);
```

## get
Получить шаблон.

```php
$template->get(); // string (JSON)
$template->get(false); // array
```