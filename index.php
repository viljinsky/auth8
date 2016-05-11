<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Auth8</title>
        <link rel="stylesheet" href="libs/auth/style.css">
        <script src="libs/auth/auth.js"></script>
    </head>
    <body>
        
        <div id="adminpanel">
        <?php
        include './libs/auth/auth.php';
        ?>
        </div>   
        
        
        <div>
            <p>Права пользователя</p>
            <?php
                if (isset($_SESSION['permission'])){
                    echo print_r($_SESSION['permission']);
                }
                $permission = new Permission(intval($_SESSION['user_id']));
                echo $permission->user_permission();
                
                
            ?>
            <?php if (isset($_SESSION['user_id']) && intval($_SESSION['role_id'])===Permission::ROLE_ADMIN) {?>
            <p>Список пользователей</p>
            <div id="userlist"></div> 
            <form id="finduser"><input name="userlocate"><input type="submit" value="Найти"></form>
            <?php }?>
        </div>
        
        
        <div>
            
            <a href="#" id="message">Написать</a>
        </div>    
        
        
        
        <script>
            var auth = new Auth(adminpanel,<?=intval($_SESSION['user_id'])?>);
            message.onclick=function(){
              auth.message();return false;
           }
           if (userlist!==null){
                auth.userlist(userlist);
           }
           
           finduser.onsubmit = function(){
               auth.locate(finduser,userlist);
               return false;
           }
           
           
        </script>
        
        <div>
        <?=HOST?>
        </div>
        
    </body>
</html>
