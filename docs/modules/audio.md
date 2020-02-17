# VkAudio
Класс для работы с методами секции `audio`.

**Обратите внимание:** для работы этого модуля необходим токен от официального приложения ВКонтакте. Тесты проводились с токеном от VK для Android.

## Оглавление
* [Исключения](#исключения)
* [Конструктор](#конструктор)
* [Получение ссылки на mp3-файл](#получение-ссылки-на-mp3-файл)
* [Работа с методами API](#работа-с-методами-api)
	- [audio.get](#get)
	- [audio.add](#add)
	- [audio.addPlaylist](#addplaylist)
	- [audio.delete](#delete)
	- [audio.deletePlaylist](#deleteplaylist)
	- [audio.edit](#edit)
	- [audio.editPlaylist](#editplaylist)
	- [audio.getPlaylists](#getplaylists)
	- [audio.getBroadcastList](#getbroadcastlist)
	- [audio.getById](#getbyid)
	- [audio.getCount](#getcount)
	- [audio.getLyrics](#getlyrics)
	- [audio.getPopular](#getpopular)
	- [audio.getRecommendations](#getrecommendations)
	- [audio.getUploadServer](#getuploadserver)
	- [audio.addToPlaylist](#addtoplaylist)
	- [audio.reorder](#reorder)
	- [audio.restore](#restore)
	- [audio.save](#save)
	- [audio.search](#search)
	- [audio.setBroadcast](#setbroadcast)
	- [execute.getPlaylist](#getplaylist)
	- [execute.getMusicPage](#getmusicpage)
	- [audio.getCatalog](#getCatalog)

## Исключения
* [ClientException](exceptions.md#client) (доступно только пользователям)
* [TokenRefreshException](exceptions.md#refresh)

## Конструктор
Принимает единственный параметр `bool $needRefresh`, от значения которого зависит, будет ли токен рефрешнуть через `auth.refresh` или нет.

```php
/* First way, only for users */
$vk = new vk('lp');
$audio = $vk->audio;

/* Second way */
$audio = new VkAudio();
```

## Получение ссылки на mp3-файл
Получив данные об аудиозаписи (используя `audio.get`, `audio.search` и другие методы), вы можете извлечь из этих данных ссылку на mp3 файл с помощью функции `getMp3Link`.

Возвращает строку, если удалось составить ссылку на файл, или `false`, если ссылку составить не получилось.

| Параметр | Тип   | Описание              |
|----------|-------|-----------------------|
| $audio   | array | Данные об аудиозаписи |

> *Обратите внимание:* ссылки на mp3-файлы привязаны к ip-адресу

```php
$needLinkAudio = $audio->get()['response']['items'][0];
$link = $audio->getMp3Link($needLinkAudio);
if($link === false) {
	echo "Не удалось получить ссылку на аудиозапись";
} else {
	echo "Ссылка на аудиозапись: {$link}";
}
```

## Работа с методами API
Почти все методы `audio` не имеют документации и доступ к ним закрыт. Вся информация добыта в ходе экспериментов и просмотра старой документации от 26 декабря 2016 года.

Все функции имеют последний (или единственный параметр) `array $params`. Он используется для передачи необязательных параметров. Например: `$audio->get(['owner_id' => 305360617])`

Не забывайте, что все ссылки на mp3-файлы привязаны к ip-адресу.

> Если заметите ошибку в этой документации, пожалуйста, создайте [issue](https://github.com/slmatthew/senses-engine/issues).

### get
Параметров функции нет. [Документация](https://web.archive.org/web/20161216125506/https://vk.com/dev/audio.get)

> По умолчанию `owner_id` равен `api_id`, указанному в `config.php`

```php
$audio->get();
```

### add
[Документация](https://vk.com/dev/audio.add)

| Параметр  | Тип | Описание                            |
|-----------|-----|-------------------------------------|
| $audio_id | int | Идентификатор аудиозаписи           |
| $owner_id | int | Идентификатор владельца аудиозаписи |

```php
$audio->add(1, 1, ['access_key' => '123abcdfgdf124']);
```

### addPlaylist
Раньше этот метод назывался `addAlbum`. [Документация](https://web.archive.org/web/20161216125245/https://vk.com/dev/audio.addAlbum)

| Параметр | Тип    | Описание           |
|----------|--------|--------------------|
| $title   | string | Название плейлиста |

```php
$audio->addPlaylist('Мой новый плейлист');
```

### delete
[Документация](https://web.archive.org/web/20161216125326/https://vk.com/dev/audio.delete)

| Параметр  | Тип | Описание                            |
|-----------|-----|-------------------------------------|
| $audio_id | int | Идентификатор аудиозаписи           |
| $owner_id | int | Идентификатор владельца аудиозаписи |

```php
$audio->delete(12345, 305360617);
```

### deletePlaylist
[Документация](https://web.archive.org/web/20161216125349/https://vk.com/dev/audio.deleteAlbum)

Отличия от документации:
* добавлен новый обязательный параметр `owner_id`
* `album_id` » `playlist_id`

| Параметр     | Тип | Описание                            |
|--------------|-----|-------------------------------------|
| $owner_id    | int | Идентификатор владельца аудиозаписи |
| $playlist_id | int | Идентификатор плейлиста             |

```php
$audio->deletePlaylist(305360617, 8000000);
```

### edit
[Документация](https://vk.com/dev/audio.edit)

| Параметр  | Тип | Описание                            |
|-----------|-----|-------------------------------------|
| $owner_id | int | Идентификатор владельца аудиозаписи |
| $audio_id | int | Идентификатор аудиозаписи           |

```php
$audio->edit(305360617, 12345, ['title' => 'Новое название']);
```

### editPlaylist
[Документация](https://web.archive.org/web/20161216125444/https://vk.com/dev/audio.editAlbum)

| Параметр     | Тип | Описание                            |
|--------------|-----|-------------------------------------|
| $owner_id    | int | Идентификатор владельца аудиозаписи |
| $playlist_id | int | Идентификатор плейлиста             |

```php
$audio->editPlaylist(305360617, 8000000, ['title' => 'Плейлист. Ещё один...']);
```

### getPlaylists
[Документация](https://web.archive.org/web/20161216125525/https://vk.com/dev/audio.getAlbums)

> По умолчанию `owner_id` равен `api_id`, указанному в `config.php`

```php
$audio->getPlaylists();
```

### getBroadcastList
[Документация](https://web.archive.org/web/20161216125549/https://vk.com/dev/audio.getBroadcastList)

```php
$audio->getBroadcastList();
```

### getById
[Документация](https://web.archive.org/web/20161216125637/https://vk.com/dev/audio.getById)

| Параметр | Тип   | Описание                                                                 |
|----------|-------|--------------------------------------------------------------------------|
| $audios  | array | Массив с идентификаторами аудиозаписей в формате `{owner_id}_{audio_id}` |

```php
$audio->getById(['1_1', '1_2']);
```

### getCount
[Документация](https://web.archive.org/web/20161216125705/https://vk.com/dev/audio.getCount)

| Параметр  | Тип | Описание                       |
|-----------|-----|--------------------------------|
| $owner_id | int | ID пользователя или сообщества |

```php
$audio->getCount(305360617);
```

### getLyrics
[Документация](https://web.archive.org/web/20161216125726/https://vk.com/dev/audio.getLyrics)

| Параметр   | Тип | Описание                                 |
|------------|-----|------------------------------------------|
| $lyrics_id | int | ID слов (приходит в объекте аудиозаписи) |

> `/n` используется как перенос строки

```php
$audio->getLyrics(123456);
```

### getPopular
[Документация](https://web.archive.org/web/20161216125746/https://vk.com/dev/audio.getPopular)

```php
$audio->getPopular();
```

### getRecommendations
[Документация](https://web.archive.org/web/20161216125807/https://vk.com/dev/audio.getRecommendations)

```php
$audio->getRecommendations();
```

### getUploadServer
[Документация](https://vk.com/dev/audio.getUploadServer)

```php
$audio->getUploadServer();
```

### addToPlaylist
[Документация](https://vk.com/dev/audio.moveToAlbum)

Скорее всего, методы `moveToAlbum` и `addToPlaylist` идентичны по функционалу.

| Параметр   | Тип   | Описание                                                                 |
|------------|-------|--------------------------------------------------------------------------|
| $owner_id  | int   | ID владельца альбома(?)                                                  |
| $audio_ids | array | Массив с идентификаторами аудиозаписей в формате `{owner_id}_{audio_id}` |

> Как можно было заметить из параметров выше, был добавлен обязательный параметр `owner_id`

```php
$audio->addToPlaylist(305360617, ['1_1', '1_2']);
```

### reorder
[Документация](https://web.archive.org/web/20161216125927/https://vk.com/dev/audio.reorder)

| Параметр   | Тип   | Описание                               |
|------------|-------|----------------------------------------|
| $audio_id  | int   | ID аудиозаписи, которую вы перемещаете |

```php
$audio->reorder(123456789, ['after' => 987654321])
```

### restore
[Документация](https://web.archive.org/web/20161216125948/https://vk.com/dev/audio.restore)

| Параметр   | Тип   | Описание                                    |
|------------|-------|---------------------------------------------|
| $audio_id  | int   | ID аудиозаписи, которую вы восстанавливаете |

```php
$audio->restore(123456789);
```

### save
[Документация](https://vk.com/dev/audio.save)

| Параметр | Тип    | Описание                                                          |
|----------|--------|-------------------------------------------------------------------|
| $server  | int    | Параметр, полученный от [audio.getUploadServer](#getUploadServer) |
| $audio   | string | Параметр, полученный от [audio.getUploadServer](#getUploadServer) |

```php
$audio->save(123, 'someStringFromVkApi');
```

### search
[Документация](https://web.archive.org/web/20161216130029/https://vk.com/dev/audio.search)

```php
$audio->search(['q' => 'Beatles']);
```

### setBroadcast
[Документация](https://web.archive.org/web/20161216130048/https://vk.com/dev/audio.setBroadcast)

```php
$audio->setBroadcast(['audio' => '1_190442705']);
```

### getPlaylist
Возвращает объект с полем `audios`, содержащий объекты, описывающие аудиозаписи.

Ниже представлены известные параметры процедуры `execute.getPlaylist`. Функция имеет один аргумент — `array $params`.

| Параметр       | Тип | Required? | Описание                                                                    |
|----------------|-----|-----------|-----------------------------------------------------------------------------|
| need_playlists | int | no        | Вернуть плейлисты или нет. По умолчанию `1`                                 |
| owner_id       | int | yes       | ID пользователя или сообщества. По умолчанию равен `api_id` из `config.php` |
| id             | int | no        | ID плейлиста. Если не указан, вернутся все аудиозаписи                      |

```php
$audio->getPlaylist();
```

### getMusicPage
Возвращает объект с полями `playlists` и `audios`. Первый представляет собой объект с полями `count` и `items`; `items` содержит массив с объектами, описывающими плейлист. Второй — объект с полями `count` и `items`; `items` содержит в себе объекты, описывающие аудиозапись.

В `playlists` и `audio` возвращаются все плейлисты и аудиозаписи пользователя. Если возникнет ошибка, в корне ответа появится поле `execute_errors`, а `playlists` и `audio` будут равны `false`.

Ниже представлены известные параметры процедуры `execute.getMusicPage`. Функция имеет один аргумент — `array $params`.

| Параметр       | Тип | Required? | Описание                                                                       |
|----------------|-----|-----------|--------------------------------------------------------------------------------|
| func_v         | int | no        | Стандартный параметр. По умолчанию `3` (для этой версии написана документация) |
| need_playlists | int | no        | Вернуть плейлисты или нет. По умолчанию `1`                                    |
| owner_id       | int | yes       | ID пользователя или сообщества. По умолчанию равен `api_id` из `config.php`    |

```php
$audio->getMusicPage(['owner_id' => 305360617]);
```

### getCatalog
Метод возвращает список музыки ВКонтакте по категориям: «Чарт ВКонтакте», «Рэп и Хип-хоп», «Рок» и так далее.

В поле `items` находятся объекты, описывающие категорию. Документация будет дополнена