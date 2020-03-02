# Исключения
На этой странице представлен список исключений, которые могут быть сгенерированы библиотекой.

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
* нельзя добавить элемент этого типа в шаблон
* такой тип нельзя указать в этой функции

В классе `vk`:
* **если код 1,** вы передали невалидные данные для авторизации
* **если код 2,** вы указали слишком старую версию API

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

<a name="msgapi"></a>
## MessageApiException
Невозможно использовать этот метод для текущего сообщения.

<a name="vkauth"></a>
## VkAuthException
Причины:
* **если код 1,** значит не удалось получить ответ от oauth-сервера
* **если код 2,** значит при получении токена произошла ошибка (у вас включена 2FA, требуется ввод капчи и т.д.)