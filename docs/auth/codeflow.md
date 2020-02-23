# Authorization Code Flow
В этом разделе описываются два класса для работы с Authorization Code Flow авторизацией: [для пользователей](https://vk.com/dev/authcode_flow_user) и [для сообществ](https://vk.com/dev/authcode_flow_group).

## AuthFlowUser
### Исключения
* [AuthEmptyParamsException](../modules/exceptions.md#authemptyparams)

### Конструктор
| Параметр       | Тип    | Описание                                     |
|----------------|--------|----------------------------------------------|
| $client_id     | int    | Идентификатор вашего приложения              |
| $client_secret | string | Секретный ключ вашего приложения             |
| $redirect_uri  | string | Адрес, куда будет перенаправлен пользователь |
| $scope         | int    | Битовая маска настроек доступа приложения    |
| $params        | array  | Дополнительные параметры _(не обязательный)_ |

```php
$auth = new AuthFlowUser(1, 'secret123', 'https://my-super-host.com/auth', 64);
```

### link
Получить сформированную ссылку.

```php
$link = $auth->link();
```

## AuthFlowGroup
Этот класс полностью наследует методы от `AuthFlowUser`, за исключением конструктора.

| Параметр       | Тип    | Описание                                     |
|----------------|--------|----------------------------------------------|
| $client_id     | int    | Идентификатор вашего приложения              |
| $client_secret | string | Секретный ключ вашего приложения             |
| $redirect_uri  | string | Адрес, куда будет перенаправлен пользователь |
| $scope         | int    | Битовая маска настроек доступа приложения    |
| $group_ids     | array  | ID групп. В запросе будут разделены запятыми |
| $params        | array  | Дополнительные параметры _(не обязательный)_ |

```php
$auth = new AuthFlowGroup(1, 'secret123', 'https://my-super-host.com/auth', 64, [1, 2, 3]);
```