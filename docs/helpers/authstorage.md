# vkAuthStorage
Класс реализует логику хранилища данных для авторизации. Вам будут нужны не все его методы.

**Обратите внимание:** библиотека работает с VK API от имени текущего пользователя. Не забывайте менять ID активного пользователя.

## Добавление данных
Добавление данных осуществляется через класс [vk](../modules/vk.md).

```php
$vk = new vk([
	'token' => 'qwerty12345',
	'type' => 'lp'
]);

$vk2 = new vk([
	'token' => '12345qwerty',
	'type' => 'lp'
]);
```

## setActive
Изменить ID активного пользователя. Возвращает:
* `true`, если всё прошло гладко
* `false`, если был передан несуществующий ID

```php
vkAuthStorage::setActive(305360617); // bool
```

## getActive
Узнать ID активного пользователя.

```php
vkAuthStorage::getActive(); // int
```

## getAviableIds
Получить список ID пользователей, доступных для авторизации.

```php
vkAuthStorage::getAviableIds(); // array
```

> Возможна проблема с коллизией ID пользователей и сообществ. В будущем она будет исправлена.

## setErrorsPeer
Установить `peer_id`, куда будут отправляться ошибки VK API. Установите `0`, чтобы отключить эту функцию.

Если при отправке сообщения возникнет ошибка, библиотека проигнорирует её, дабы не было рекурсии. Будьте внимательней с этой функцией при использовании нескольких аккаунтов.

```php
vkAuthStorage::setErrorsPeer(2e9 + 1); // void

// disabling:
vkAuthStorage::setErrorsPeer(0);
```

![image](https://user-images.githubusercontent.com/36668268/75895356-96dcdd00-5e46-11ea-8a59-81310415610a.png)

## getErrorsPeer
Получить `peer_id`, куда отправляются ошибки VK API.

```php
vkAuthStorage::getErrorsPeer(); // int
```