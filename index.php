<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Тест Авторизации</title>
        <link   rel = "stylesheet" href = "libs/auth/style.css">
        <script src = "libs/auth/auth.js"></script>
        <style>
            body,html{height: 100%; margin: 0;padding: 0}
            .page-cover{min-height: 100%;}
            .hfooter{height: 100px;}
            footer{height: 100px; margin-top: -100px;}
            body{font-family: Arial;}
            header {padding: 10px;}
            header,footer {background: #ccc;}
            body{background: #f0f0f0;}
            .content {max-width: 800px;  margin: 0 auto; background: #fff; }
            
            td {white-space: nowrap;}
            tr:nth-child(odd) {background: #f0f0f0;}
        </style>
    </head>
    
    <body>
        <div class="page-cover">
            <header>
             <div id = "admin">   
            <?php
                // старт сессии, инициализация текущего пользователя
                // и выаод строки аутендификации
                include './libs/auth/auth.php';
            ?>
            </div>
            </header>
            
            <div class="content">

                <a href="#" onclick="location.reload();">Домой</a>
                <?php if (intval($_SESSION['role_id'])===3) { ?>
                <a href="#" id="users" >Список пользователей</a>
                <?php } ?>

                <div id="userlist"></div>

            </div>
            <div class="hfooter"></div>
        </div>
        <footer>
            2016 &copy; <a href="#" id="message" title="Написать сообщение">Ильинский В.В.</a>
        </footer>
        
        <script>
            

            var auth = new Auth(admin,<?= intval($_SESSION['user_id'])?>);
            
            message.onclick=auth.message;
            
            if (typeof  users!=='undefined'){
                users.onclick=function(){
                    auth.userlist(userlist);
                };
            }
            
        </script>
    </body>
</html>
