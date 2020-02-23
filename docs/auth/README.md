# Auth
Модуль для работы с oauth-авторизацией и методами `auth`.

## Инициализация
В библиотеке существует несколько классов для работы с авторизацией. Каждый из них имеет метод `setHost`, который будет описан ниже.

```php
$authPassword = new AuthPassword(); // авторизация по логину и паролю
$authImplictUser = new AuthImplictUser(); // Implict Flow for users
$authImplictGroup = new AuthImplictGroup(); // Implict Flow for groups
$authFlowUser = new AuthFlowUser(); // Authorization Code Flow for users
$authFlowGroup = new AuthFlowGroup(); // Authorization Code Flow for groups
```

### Установка oauth-хоста
Возможно, вам будет необходимо изменить стандартый хост oauth (`oauth.vk.com`) на другой. Вы можете сделать это с помощью метода `setHost`.

```php
// на месте $auth может быть $authPassword, $authImplictUser, $authImplictGroup, $authFlowUser, $authFlowGroup
$auth->setHost('oauth.mysuperproxyserver.dev');
```

## Обзор
* [Авторизация через официальные и проверенные приложения](official.md)
  - [Использование trusted_hash](trustedhash.md)
* [Implict Flow](implict.md)
	- [Для пользователей](implict.md#authimplictuser)
	- [Для сообществ](implict.md#authimplictgroup)
* [Authorization Code Flow](codeflow.md)
	- [Для пользователей](codeflow.md#authflowuser)
	- [Для сообществ](codeflow.md#authflowgroup)