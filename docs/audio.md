# Audio
Модуль для работы с методами секции `audio`.

## Навигация
* [Конструктор](#конструктор)
* [Исключения](#исключения)
	- [Параметры и пример](#параметры-и-пример)
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

## Конструктор
Чтобы начать работу с этим классом, вам необходим токен пользователя. Его необходимо получить от имени приложения *VK для Android* (тестировалось только с таким токеном).

Если этот токен не работает, передайте `true` в конструктор, чтобы токен был переполучен через метод `auth.refresh`.

### Исключения
* `ClientException`, если `type` в конфиге не равен `user`
* `TokenRefreshException`, если не удалось рефрешнуть токен, т.е. получена ошибка при выполнении `auth.refresh`

### Параметры и пример

| Параметр     | Тип  | Описание                                         |
|--------------|------|--------------------------------------------------|
| $needRefresh | bool | Нужно ли переполучить токен через `auth.refresh` |

```php
$audio = new VkAudio();
$audio = new VkAudio(true); // если не работает с обычным токеном VK для Android
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