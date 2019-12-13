# Класс SBSC (Step-by-step commands)
Класс, основанный на [BotEngine](https://github.com/slmatthew/senses-engine/blob/master/docs/botengine.md#класс-botengine) и позволяющий создать команды с поэтапным вводом данных (step-by-step commands). Вместо BotEngine вы можете создавать экземпляр класса SBSCommands:
```php
$be = new SBSCommands();

// commands

$dh = new DataHandler('lp', $be);
```

> Если вам не нужно создавать команды с поэтапным вводом данных, такие как оформление заказа и прочие, используйте класс BotEngine.

> SBSC-команды работают только при использовании LP.

## addSbsCommand
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

## checkSbsCommand
Проверка типа команды. Возвращает `false`, если команды не существует. Принимает единственный `string` параметр — имя команды.

```php
if($be->checkSbsCommand('order') == 'payload') echo "order - payload\n";
if($be->checkSbsCommand('заказ') == 'text') echo "заказ - text\n";
if($be->checkSbsCommand('randomWord') === false) echo "randomWord - false\n";
```

## handleSbsCommand
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

## checkAllCommands
Слегка измененная функция из оригинального BotEngine, включающая в себя логику обработки sbs-команд.

| Параметр     | Тип    | Описание                   |
|--------------|--------|----------------------------|
| $payloadName | string | Название payload-команды   |
| $textName    | string | Название текстовой команды |
| $data        | array  | Сообщение                  |

[Пример использования](https://github.com/slmatthew/senses-engine/blob/master/docs/botengine.md#checkallcommands)