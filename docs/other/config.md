# Конфигурация
Главный файл конфигурации — `config.php`. Его название можно изменить, но не забудьте изменить `loader.php`. В корневой папке библиотеки есть пример такого файла `config.php.example`.

Конфигурация представляет собой файл с переменной `$config`, содержащей информацию о текущем клиенте. Лучше не менять название этой переменной.

| Поле    | Тип    | Описание                                                   |
|---------|--------|------------------------------------------------------------|
| type    | string | `user` или `community`: чей токен используется для работы  |
| token   | string | `access_token`                                             |
| secret  | string | Секретный ключ из настроек Callback API сервера            |
| api_id  | int    | ID текущего пользователя или сообщества                    |
| version | string | Версия VK API. По умолчанию `5.103`                        |
| dev     | mixed  | Если это поле указано, то `CURLOPT_SSL_VERIFYPEER = false` |

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