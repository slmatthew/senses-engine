# Конфигурация
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