<?php

    session_start(); 
    
    include './libs/connect.php';
    
    
    if (!isset($_SESSION['user_id'])){
        
        $_SESSION['user_id']=null;
        $_SESSION['user_name']=null;
        $_SESSION['role_id']=null;
        
        $login = filter_input(INPUT_COOKIE,'login');
        $pwd = filter_input(INPUT_COOKIE,'pwd');
        if (isset($login) && isset($pwd)){

            $sql    = "select user_id,concat(last_name,' ' ,first_name),role_id \n"
                  ." from users  where (login='$login' or email='$login') and pwd='$pwd'";
            $result = mysql_query($sql) or die(mysql_error());
            if ($result && mysql_num_rows($result)===1){
                $data = mysql_fetch_array($result);
                list($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['role_id']) = $data;
                mysql_query('insert into visits (user_id) values('.$_SESSION['user_id'].')') or die(mysql_error());
            } 
        } 
    }
      $user_id=$_SESSION['user_id'];
?>
        
<ul class="admin">
    <?php if (!isset($_SESSION['user_id'])) {?>
    <li><a href="#" data-action="login">Вход</a></li>
    <li><a href="#" data-action="register">Регистрация</a></li>
    <?php } else {?>
    <li>Здравствуйте <a href="#" data-action="userinfo"><?=$_SESSION['user_name']?></a>.</li>
    <li><a href="#" data-action="logout">Выход</a></li>
    <?php } ?>
</ul>




