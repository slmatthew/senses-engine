# Авторизация через официальные и проверенные приложения
В этом разделе описан процесс авторизации через официальные и проверенные приложения ВКонтакте.

## auth
Метод для авторизации через логин и пароль.

```php
$auth = new AuthPassword();
```

### Параметры
Параметры, выделенные курсивным шрифтом, являются обязательными.

| Параметр        | Тип    | Описание                                       |
|-----------------|--------|------------------------------------------------|
| _$app_          | string | Кодовое имя приложения для авторизации         |
| _$username_     | string | Логин                                          |
| _$password_     | string | Пароль                                         |
| $trusted_hash   | string | Подробнее [здесь](#использование-trusted_hash) |
| $params         | array  | Дополнительные параметры                       |

### Список приложений
Приложения, которые вы можете использовать при таком методе авторизации

| ID | Приложение      | Название в `$app` |
|----|-----------------|-------------------|
| 0  | Android         | anroid            |
| 1  | iPhone          | iphone            |
| 2  | iPad            | ipad              |
| 3  | Windows PC      | windowspc         |
| 4  | Kate Mobile     | kate              |
| 5  | VK Messenger    | vkmessenger       |
| 6  | Snapster        | snapster          |
| 7  | Nokia (Symbian) | nokia             |
| 8  | Windows Phone   | windowsphone      |
| 9  | Lynt            | lynt              |
| 10 | Vika            | vika              |

### Исключения
* [AuthRequestException](../modules/exceptions.md#authreq)
* [AuthBannedException](../modules/exceptions.md#authbanned), в `message` будет json-строка с данными от oauth
* [AuthAppException](../modules/exceptions.md#authapp)

### Пример
```php
$auth->auth('iphone', 'slmatthew666', 'mySuperSecretPassword');
```

### Обработка ошибок
Метод `auth` возвращает ассоциативный массив, с помощью которого вы можете определить успешность авторизации.

| Поле         | Тип      | Описание                                                                                                                       |
|--------------|----------|--------------------------------------------------------------------------------------------------------------------------------|
| success      | bool     | Определяет успешность авторизации                                                                                              |
| data         | array    | Ассоциативный массив с полями `data` и `headers`. В первом содержится ответ от oauth, во втором — заголовки ответа             |
| needSendCode | bool     | Необходимо ли подтвердить авторизацию, отправив код                                                                            |
| sendCode     | callable | Функция, приниимающая аргумент `$code` (любого типа). Повторяет процесс авторизации, добавляя `code` к запросу к oauth-серверу |

```php
$result = $auth->auth('iphone', 'slmatthew666', 'mySuperSecretPassword');
```

Авторизация является успешной, если выполняется `$result['success']`. Однако, библиотека не обрабатывает случай, когда у пользователя включена 2FA.

Специально для таких случаев в ответе предусмотрено поле `sendCode`, которое позволяет переавторизоваться, используя код, введеный пользователем. Вот пример простого консольного приложения:

```php
$result = $auth->auth('iphone', 'slmatthew666', 'mySuperSecretPassword');
if($result['success']) {
	if($result['needSendCode']) {
		$code = readline("Введите код 2FA: ");
		print_r($result['sendCode']($code));
	} else {
		$data = json_decode($res['data']['data'], true);
		echo "Авторизация прошла успешно, токен: {$data['access_token']}";
	}
} else echo "Не удалось авторизоваться";
```

`$result['success']` может быть `false`, если требуетя ввести капчу. В таком случае структура ответа будет такой:

| Поле        | Тип      | Описание                                                                                                                       |
|-------------|----------|--------------------------------------------------------------------------------------------------------------------------------|
| success     | bool     | Определяет успешность авторизации. В рассматриваемом случае — `false`                                                          |
| desc        | string   | Описание ошибки. В рассматриваемом случае — `need_captcha`                                                                     |
| captcha_sid | string   | ID капчи. Вы можете протестировать это с помощью VK API метода `captcha.force`                                                 |
| send        | callable | Функция, аналогичная `sendCode` при успешной авторизации. Принимает аргумент `string $code`, в котором должен быть код с капчи |

```php
if(!$result['success']) {
	$code = readline("Please, open https://api.vk.com/captcha.php?sid={$result['captcha_sid']}&s=1 in browser and enter captcha: ");
	print_r($result['send']($code));
}
```

## validateByPhone
Обёртка над `auth.validatePhone`. Используется, чтобы код для 2FA пришлел на привязанный номер телефона.

| Параметр | Тип    | Описание                                                                                                    |
|----------|--------|-------------------------------------------------------------------------------------------------------------|
| $app     | string | [Приложение для авторизации](). Желательно использовать то же, что было в [auth](#auth) |
| $sid     | string | `validation_sid` от oauth-сервера                                                                           |

```php
// Мы точно уверены, что включена 2FA
$result = $auth->auth('iphone', 'slmatthew666', 'mySuperSecretPassword');
$auth->validateByPhone('iphone', json_decode($result['data']['data'], true)['validation_sid']);
// Код приходит на привязанный номер телефона
```