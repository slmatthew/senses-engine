# Модуль Upload. Загрузка файлов
Вы можете использовать этот модуль для загрузки файлов.

## Инициализация
```php
$u = new Upload();
// your code
```

## photoAlbum. Фотографии
Загрузка фотографии или фотографий. Может выбросить исключение `ApiException`.

| Параметр      | Тип             | Описание                                                       |
| --------------|-----------------|----------------------------------------------------------------|
| $files        | string or array | Строка с url файла или массив с url файлов: `['url1', 'url2']` |
| $uploadParams | array           | Параметры для метода `photos.getUploadServer`                  |
| $saveParams   | array           | Параметры для метода `photos.save`                             |

```php
$u = new Upload();
$u->photoAlbum('website.com/image.png', [], []);
$u->photoAlbum(['website.com/image1.png', 'website.com/image2.png', 'website.com/imageN.png'], [], []);
```