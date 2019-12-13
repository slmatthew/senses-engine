# Модуль Keyboard
Модуль используется для генерации клавиатур.

## Конструктор
| Параметр  | Тип  | Описание                                           |
|-----------|------|----------------------------------------------------|
| $one_time | bool | Скрывать ли клавиатуру после первого использования |
| $inline   | bool | Должна ли клавиатура отображаться внутри сообщения |

```php
$fkb = new Keyboard(true, false);
$skb = new Keyboard(false, true); // при $inline = true $one_time может иметь любое значение
```

## inline и oneTime
Вы можете задать значение `one_time` и `inline` после создания экземпляра класса. Для этого используйте методы `oneTime()` и `inline()`, которые принимают единственный bool-параметр `$enabled`.

```php
$kb->oneTime(false);
$kb->inline(true);
```

## addButton
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

## Другой способ добавления кнопок
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

## addLine
Добавление новой строки в клавиатуру.

```php
$kb->addLine();
```

## get
Получить построенную клавиатуру. Если в первый (и единственный) параметр передать `true`, вернётся JSON.

```php
$kb->get(); // array
$kb->get(true); // string (JSON)
```

> До версии `0.6` функция имела название `getKeyboard`.