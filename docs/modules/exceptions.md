# Исключения
На этой странице представлен список исключений, которые могут быть сгенерированы библиотекой.

<a name="config"></a>
## ConfigException
Причины:
* не инициализирована переменная `$config`
* выполняется `is_null($config)`
* выполняется `empty($config)`

<a name="type"></a>
## TypeException
Причина: указан неверный `$client_type` (тип получения данных)

<a name="requests"></a>
## RequestsException
Причина: не удалось загрузить модуль [Requests](requests.md)

<a name="lp"></a>
## LongpollException
Причина: не удалось получить новые данные LP-сервера

<a name="emptyattach"></a>
## EmptyAttachException
Причина: не было инициализировано вложение

<a name="param"></a>
## ParameterException
Причины:
* невалидное значение `$attach`
* указано больше 5 файлов
* передан невалидный параметр

<a name="api"></a>
## ApiException
Причина: ошибка выполнения API метода

<a name="client"></a>
## ClientException
Причина: указан тип клиента, который не соответствует требованиям

<a name="refresh"></a>
## TokenRefreshException
Причина: получена ошибка при выполнении метода `auth.refresh`

<a name="authreq"></a>
## AuthRequestException
Ошибка при запросе к oauth-серверу

<a name="authbanned"></a>
## AuthBannedException
Аккаунт заблокирован.

<a name="authapp"></a>
## AuthAppException
Указано неизвестное приложение.

<a name="authemptyparams"></a>
## AuthEmptyParamsException
Не указаны параметры.