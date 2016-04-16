# Идентификация пользователей

Модуль предназначен для встраивания в вэб-сайт для идентификации пользователей.

* Регистрация пользователя
* Назначение прав пользователю

## Перед началом установки

* убедится в работоспособности сервера MySQL.
* выполнить скрипт auth8.sql 


## Установка модуля

1. Выполнить скрит auth8.sql на сервере MySQL
1. разместить папку auth в libs
2. 

## Встраивание в сайт

раздел head

    <link   rel = "stylesheet" href = "libs/auth/style.css">
    <script src = "libs/auth/auth.js"></script>


раздел body

    <?php
        // старт сессии, инициализация текущего пользователя
        // и выаод строки аутендификации
        include './libs/auth/auth.php';
    ?>

в конце body

    <script>
        var auth = new Auth(admin,<?= intval($_SEESION[$user_id]) ?>');
        message.onclick=auth.message;
        if (typeof  users!=='undefined'){
            users.onclick=function(){
                auth.userlist(userlist);
            };
        }
    </script>







