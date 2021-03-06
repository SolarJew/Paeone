[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://gplv3.fsf.org/)

| Paeone   | Open Source Free System                         |
|:--------:|:------------------------------------------------|
| Authors  | Yuri Nikolaev, Maksim Kudryavcev                |
| Contact  | tevaz@yandex.ru                                 |
| License  | GNU GPL v3                                      |


System requirements
-------------------
 - WebServer:	`Apache 2.2, 2.4` `Nginx 1.11, 1.12`
 - PHP:		`5.5.*` `7.*`
 - DB:		`MySQL 5.5` and high or `MariaDB: 10` and high

Install
-------
1. Скачайте установочный архив https://yadi.sk/d/hiTHBqJdIuIhGg.
2. Распакуйте архив в корневую папку Вашего хостинга
2.1. Или распакуйте архив в директорию, которую определите корневой для этого приложения
3. Создайте БД на Вашем хостинге
4. В браузере откройте ссылку: `http://ваш_сайт.com/install.php`
4.1. Если пункт `2.1` - в браузере откройте ссылку: `http://ваш_сайт.com/директория/install.php`

> Для пункта `2` Вместо ваш_сайт.com используйте доменное имя прикрепленное к вашему хостингу.
> Для пункта `2.1` Вместо ваш_сайт.com/директория используйте доменное имя прикрепленное к вашему хостингу и имя директории.

> Внимание
> В целях безопасности Paeone не поддерживает возможность работы с базой данных без логина и пароля. При попытке использовать пустой пароль для базы данных система выдаст сообщение об ошибке.

Language support
----------------

 - Поддерживает использование любых языков. В качестве примера используются русский и английский

Description
-----------
В данном проекте предпринята попытка реализовать возможность подключения или отключения `условного блока` без внесения изменений в ядро системы.

Для увеличения функционала программного обеспечения достаточно создать независимый блок кода, объявить его в html шаблоне родительского блока (там, где Вам это требуется) в виде метки `[*include | имя_блока*]` и объявить его подключение в родительском `view/Имя_блока.php` объектом `$this->connection(имя блока)`. Корневым блоком для всех последующий подключений является `View/Content.php`.
Стандартным блоком обработки страниц является `View/Page.php`. Если Вам требуется заменить на одной или многих страницах стандратный обработчик на свой блок, то просто укажите его имя в ячейке `view`, таблицы `pages`, в БД.

> Подключение дочерних блоков должно производиться только после отработки всех элементов родительского блока.

### Блок состоит из трёх частей:

 1. View/Имя_блока.php
 2. Design/имя_темы/Имя_блока.html
 3. Functions/Имя_блока.php

### В данной структуре возможны варианты: 

 - Только `1 и 2`
 - Только `3 и 2`
 - Только `3`
 - Полный `1, 2 и 3`

> Устанавливать метку `[*include | имя_блока*]` в родительском html нужно, только если существует `View/Имя_блока.php`

### View/Имя_блока.php:

Файл в директории View. Отвечает за обработку и вывод шаблона при запросе к странице сайта вместе с другими блоками `в том случае, если он объявлен на запрашиваемой странице`. В момент исполнения в его `$this` вкладываются объекты ядра `настройки, доступные языки, get, post, адрес запроса, язык запроса, объекты запросов к БД`, разделённый на элементы шаблон блока `$this->template`. Так же становится доступной функция подключения дочерних блоков.

> Для данной части блока наличие файла `Design/имя_темы/Имя_блока.html` Обязательно.

### Структура View/Имя_блока.php:

```html
class Имя_блока extends Container
{
    // Обязательный элемент
    function __construct(){
        // Обязательный элемент
        parent::__construct();
        
        // Обязательный элемент. Ответ необходимо вложить в $this->template в виде html строки.
        $this->template = $this->view();
        
        // Не обязательный элемент. Подключение дочернего блока
        $this->connection('language');
        
        // Последовательность принципиально важна! В начале всегда исполняется код самого блока, затем подключается дочерний блок.
    }
    
    // Не обязательный элемент объявленный в __construct().
    private function view()
    {
        $url = '[*url | ADDRESS*]'.($this->request->language != $this->config->DEFAULT_LANGUAGE ? '[*url | TYPE*]' : '/');
        $result = str_replace('[*func | url*]', $url, $this->template['content']);
        
        return $result;
    }
}
```

### Design/имя_темы/Имя_блока.html:

Файл в директории Design/имя_темы/. Является шаблоном блока разделённым метками на элементы.

### Метки используемые в шаблоне:

 - `[*data | имя_метки*]` – используется для вывода данных из БД. Имя должно соответстовать ключу в таблице.
 - `[*url | имя_метки*]` – используется для установки головной и/или конечной части адреса URL. Доступны `[*url | ADDRESS*] и [*url | TYPE*]`
 - `[*func | имя_метки *]` – используется текущим блоком для собственных нужд. Имя может быть любым.
 - `[*lang | имя_метки *]` – используется для вывода данных из языкового пакета. Имя должно соответствовать ключу в языковом файле.
 - `[*split | имя_метки *] html код [*/split*]` – используется для разграничения шаблона на элементы. Имя может быть любым, но уникальным среди других меток `split`. На основе этих меток в объект `$this->template` вкладывается массив элементов, где ключ, это имя метки, значение html код.
 - `[*include | имя_метки *]` – используется для вывода html кода дочернего блока. Имя должно соответствовать названию блока.

> Данный файл является обязательным для `View/Имя_блока.php` и не является обязательным для `Functions/Имя_блока.php`.

### Пример Design/имя_темы/Имя_блока.html:

```html
[*split | language*]
<div class="boxInput">
    <div class="inputGroupAddon" id="langue">
        <i class="flag [*func | alpha_two*]"></i>
    </div>
    <div class="selectPointList langueList" id="langueList">
        [*func | list_language*]
    </div>
</div>
[*/split*]

[*split | language_elem*]
<a href="[*func | url*]">
    <div class="pointStyleList optLangList">
        <i class="flagInList [*func | alpha_two*]"></i>[*func | name_lang*]
    </div>
</a>
[*/split*]
```

### Functions/Имя_блока.php:

Файл в директории Functions. Отвечает за обработку прямых `GET и POST` запросов `http://имя_сайта.ru/имя_блока.func`.
В момент исполнения в его `$this` вкладываются объекты ядра `настройки, доступные языки, get, post, адрес запроса, язык запроса, объекты запросов к БД`, разделённый на элементы шаблон блока `$this->template` (если шаблон существует).

### Структура Functions/Имя_блока.php:

```html
class Имя_блока extends Container
{
    // Обязательный элемент.
    function __construct(){
        //self::$permitGet = true; // Разрешает прямой GET запрос (по умолчанию, только POST)
        //self::$permitGlobal = true; // Разрешает запрос с других ресурсов (по умолчанию, только локальный запрос. Требует token)
        //self::$permitHtml = true; // Осуществляет ответ с заголовком text/html (по умолчанию application/json)
        
        // Обязательный элемент.
        parent::__construct();
        
        // Не обязательный элемент.
        $answer = $this->checkUser();
        
        // Не обязательный элемент. Ответ необходимо вложить в $this->template в виде html строки.
        $this->template = $this->view($answer);
    }
    
    private function checkUser()
    {
        $result = array();
        if(isset($this->request->post['login']) && isset($this->request->post['password'])){
            $answer = $this->db->select("idUser, passUser","users","loginUser = '".$this->request->post['login']."' and statusUser = '1'");
            if(isset($answer[0]['idUser'])){
                if(md5($this->request->post['password']) == $answer[0]['passUser']){
                    $_SESSION['user'] = $answer[0]['idUser'];
                }else{
                    $result['error'] = '[*lang | error_login_pass*]';
                }
            }else{
                $result['error'] = '[*lang | error_login_pass*]';
            }
        }else{
            $result['error'] = '[*lang | error_params*]';
        }
        
        return $result;
    }
    
    private function view($answer)
    {
        if(isset($answer['error'])){
            $str = str_replace('[*func | error_text*]', $answer['error'], $this->template['error_mess']);
            $result = str_replace('[*func | message*]', $str, $this->template['error_answer']);
        }else{
            $result = $this->template['posit_answer'];
        }
        
        return $result;
    }
}
```

### Структура Базы Данных:

 - pages
 - users `В качестве примера работы блока`

### Таблица pages:

 - id: Идентификатор записи `Уникален для каждой записи`
 - idPage: Идентификатор страницы `Общий для страниц с разным языком, уникален относительно других страниц`
 - category: Идентификатор категории `Общий для страниц с разным языком. Содержит idPage родительской страницы (по умолчанию 0, корневая страница)`
 - language: Идентификатор языка страницы `Уникален для страниц с разным языком, общий относительно других страниц. Использует записи alpha2`
 - view: Идентификатор блока обработчика `Содержит имя блока или пуст`
 - name: Имя страницы
 - title: Заголовок страницы
 - keywords: Ключевые слова страницы
 - description: Краткое описание страницы
 - text: Контент страницы
 - address: Адрес страницы `может являться директорией в адресе дочерней страницы`
 - robotTxt: Идентификатор отключения страницы в генерируемом файле robot.txt `За генерацию файла отвечает соответствующий блок`
 - siteMap: Идентификатор включения страницы в генерируемом файле sitemap.xml `За генерацию файла отвечает соответствующий блок`
 - creatDate: Дата создания страницы
 - status: Идентификатор видимости страницы

> Ячейки требующиеся ядру: `id, idPage, category, language, view, address, status`. Остальные ячейки можно изменять, добавлять, удалять.

### Таблица users:

 - idUser: Идентификатор записи `Уникален для каждой записи`
 - loginUser: Идентификатор пользователя `Уникален для каждого пользователя`
 - passUser: Пароль пользователя
 - surnameUser: Фамилия пользователя
 - nameUser: Имя пользователя
 - dateBirthUser: Дата рождения пользователя
 - genderUser: Пол пользователя
 - imgUser: Ссылка на изображение загруженное пользователем
 - phoneUser: Телефон пользователя
 - emailUser: Email пользователя
 - creatDateUser: Дата регистрации пользователя
 - statusUser: Идентификатор активной/не активной записи

License
-------
 - [License](http://gplv3.fsf.org/)

P.S.
----
Ваши идеи, замечания и любая помощь, будут приняты с благодарностью.
