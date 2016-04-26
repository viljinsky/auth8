<?php

session_start();

include_once '../connect.php';

include './consts.php';

include './Permission.php';

//-------------------------------------------------
$command = filter_input(INPUT_POST,'command');
if (isset($command)){
    switch ($command){
        # ренистрация
        case 'register':
            echo register();
            break;
        # подтверждение регистрации
        case 'confirm':
            echo confirm();
            break;
        # вход
        case 'login':
            echo login();
            break;
        # выход
        case 'logout':
            logout();
            break;
        # забыл
        
        case 'forget':
            forget();
            break;
        # восстановление
        case 'restore':
            restore();
            break;
        
        # сообщение пользователя 
        case 'message':
            echo message();
            break;
        # информация для пользователя
        case 'userinfo':
            echo read_userinfo();
            break;
        
        case 'update_userinfo':
            echo update_userinfo();
            break;
        
        #информация для администраторв
        
        case 'userlist':
            echo userlist();
            break;
        case 'permission':
            permission();
            break;
        case 'permission_update':
            permission_update();
            break;
        case 'delete':
            echo delete_user();
            break;
    }
}
        

/**
 * Вход пользователя
 * @return type
 */    
function login(){

    $login = filter_input(INPUT_POST,'login');
    $password =filter_input(INPUT_POST,'password');
    $remember_me = filter_input(INPUT_POST,'remember_me');

    $pwd = md5($password.TOPSICRET);

    $sql ="select user_id,concat(last_name,' ',first_name),role_id,email,email_confirmed from users where "
         ." (login='$login' or email='$login') and pwd='$pwd'";
    $result =  mysql_query($sql);
    if ($result){
        if (mysql_num_rows($result)===1){
            $data = mysql_fetch_array($result);
            list($user_id,$user_name,$role_id,$email,$email_confirmed) = $data;

            if (!$email_confirmed){
                return '{"error":'.ERROR_NOT_CONFIRMED.',"message":"Электронный адрес, который Вы указали при регистрации - не поддверждён"}';
            }
            
            mysql_query("insert into visits (user_id) values ($user_id)");

            $_SESSION['user_id']    = $user_id;
            $_SESSION['user_name']  = $user_name;
            $_SESSION['role_id']    = $role_id;

            if ($remember_me){
                    setcookie('login', $login,time()+3600*24*31,'/');
                    setcookie('pwd',$pwd,time()+3600*24*31,'/');
            }
            return    '{"error":'.ERROR_OK.'}';
            
            
        } else {
          return '{"error":'.ERROR_BAD_PASSWORD.',"message":"Неверный логин или пароль"}';    
        }
        
    } else {
        return '{"error":'.ERROR_SQL.',"message":"'.mysql_error.',"sql":"'.$sql.'"}';        
    }

}

function logout(){
    setcookie('login','',0,'/');
    setcookie('pwd','',0,'/');

//    session_start();
    session_unset();
    echo 'LOGOUT';
//    header('Location: ./');
}
    
/**
 * Регистрация пользователя
 *  register->confirm
 *  Письмо со ссылкой на САЙТ !!! 
 * @return type
 */

function register(){

    $secret = $_SESSION['secret'];

    $login      = filter_input(INPUT_POST,'login');
    $password1  = filter_input(INPUT_POST,'password1');
    $password2  = filter_input(INPUT_POST,'password2');
    $last_name  = filter_input(INPUT_POST,'last_name');
    $first_name = filter_input(INPUT_POST,'first_name');
    $email      = filter_input(INPUT_POST,'email');
    $captcha    = filter_input(INPUT_POST, 'captcha');


    if ($captcha!==$secret):
        return '{"error":'.ERROR_CAPTCHA.',"message":"Неверно введено число"}';
    endif;

    if ($password1!==$password2 || strlen($password1)<2):
        return '{"error":'.ERROR_BAD_PASSWORD.',"message":"Пароль введён не верно"}';
    endif;


    $result = mysql_query('select count(*) as count from users where login="'.$login.'" or email="'.$email.'"');
    $data = mysql_fetch_array($result);
    if ($data['count']>0):
        return '{"error":'.ERROR_USER_EXISTS.',"message":"Такой логин или емейл уже существует"}';
    endif;

    $pwd = md5($password1.TOPSICRET);

    $sql = "insert into users (last_name,first_name,email,login,pwd,role_id) "
          ."values ('$last_name','$first_name','$email','$login','$pwd',".ROLE_GUEST.")";
    if (!mysql_query($sql)){
        return '{"error":'.ERROR_SQL.',"message":"'.mysql_error().'","sql":"'.$sql.'"}';
    }

    if (mysql_affected_rows()==1){

        $user_id = mysql_insert_id();

        $link = LOCATION.'/?register='.$user_id.'&hash='.$pwd;
        
        $subject = 'Составитель расписания.Регистрация ';
        
        $message = 
            "<p>".
            'Для завершения регистрации перейдите по ссылке : <a href="'.$link.'">Завершить регистрацию</a>'.
            "</p>".
            "<p>".
            "Если Вы не регистрировались на сайте Составитель расписания (письмо пришло к Вам по ошибке),".
            "просто проигнорируйте его".            
            "</p>";
        


        $from = 'timetabler@narod.ru';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: "."=?utf-8?B?".base64_encode('Ильинский В.В.')."?="." <$from>\r\n";

        mail($email, $subject, $message,$headers);

        return '{"error":'.ERROR_OK.',"message":"-"}';
    } else  {
        return '{"error":'.ERROR_UNKNOW.',"message":"Неизвестная ошибка"}';
    }

}

/**
 * Чтение мнформации о пользователе
 * @return type
 */
function read_userinfo(){

    $user_id = filter_input(INPUT_POST,'user_id');

    $sql = "select login,last_name,first_name,email,allow_to_notify from users "
          ." where user_id=$user_id";

    $result = mysql_query($sql);
    if ($result && mysql_num_rows($result)===1){
        $data = mysql_fetch_array($result);
        list($login,$last_name,$first_name,$email,$allow_to_notify)=$data;
            return '{"error":'.ERROR_OK.',"message":"Всё хорошо",'
                    .'"user_id":'.$user_id.','
                    .'"login":"'.$login.'",'
                    .'"first_name":"'.$first_name.'",'
                    .'"last_name":"'.$last_name.'",'
                    .'"email":"'.$email.'",'
                    .'"allow_to_notify":'.$allow_to_notify.'}';
        
    } else {
        return '{"error":'.ERROR_SQL.',"message":"'.  mysql_error().'","sql":"'.$sql.'"}';
    }
    
}

/**
 * Обновление информации о пользователе
 * @return type
 */
function update_userinfo(){
    $user_id    = filter_input(INPUT_POST,'user_id');
    $first_name = filter_input(INPUT_POST,'first_name');
    $last_name  = filter_input(INPUT_POST,'last_name');
    $allow_to_notify = filter_input(INPUT_POST,'allow_to_notify');
    if (empty($allow_to_notify)){        
        $allow_to_notify= 'false';
    }
    

    $sql = "update users set first_name='$first_name',last_name='$last_name',allow_to_notify=$allow_to_notify where user_id=$user_id";
    if (!mysql_query($sql)){
        return '{"error":'.ERROR_SQL.',"message":"'.mysql_error().'","sql":"'.$sql.'"}';
    }

    $_SESSION['user_name']=$first_name.' '.$last_name;
    return '{"error":'.ERROR_OK.',"message":"Изменения успешно внесены"}';

}


/**
 * Отправка сообщения пользователя администратору с сайта
 * @return string
 */
function message(){
    
    $secret     = $_SESSION['secret'];

    $user_name  = filter_input(INPUT_POST,'user_name');
    $email      = filter_input(INPUT_POST,'email');
    $subject    = filter_input(INPUT_POST,'subject');
    $text       = htmlspecialchars(filter_input(INPUT_POST,'text'),ENT_QUOTES);
    $captcha    = filter_input(INPUT_POST,'captcha');

    $to = 'timetabler@narod.ru';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: "."=?utf-8?B?".base64_encode($user_name)."?="." <$email>\r\n";

    if ($secret!=$captcha){
        return '{"error":"1","message":"captcha '.$captcha.' secret '.$secret.'"}';
    }

    if (mail($to,$subject,'<b>Текст сообщения</b><div>'.$text.'</div>',$headers)){
        return '{"error":0,"message":"Сообщение отправлено"}';
    } else {
        return '{"error":32,"message":"Ошибка при отправке сообщения"}';
    }

}
/**
 * Пользователь подтвердил емейл
 * Статус пользователя меняетсяс 0 на 2
 * @return type
 */
function confirm(){

    $user_id = filter_input(INPUT_POST,'user_id');
    $hash= filter_input(INPUT_POST, 'hash');

    $result = mysql_query("select user_id,concat(last_name,' ',first_name),role_id \n"
            ."from users where user_id=$user_id and pwd='$hash'") or die(mysql_error());

    if (mysql_num_rows($result)===1){
        $data = mysql_fetch_array($result);

        mysql_query("update users set email_confirmed=true,role_id = ".ROLE_USER." where user_id=$user_id") or die(mysql_error());
        list($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['role_id'])=$data;
        return '{"error":'.ERROR_OK.',"message":"Вы успешно зарегистрировались"}';
    }
    return '{"error":'.ERROR_UNKNOW.',"message":"Ошибка при подтверждении адреса  '.  mysql_error().'"}';

}

/**
 * Пользователь забыл пароль
 *  forget -> restore
 * @return type
 */
function forget(){

    $login_or_email = urldecode(filter_input(INPUT_POST,'login_or_email'));

    $sql = "select email,pwd,user_id,concat(last_name,' ',first_name),email_confirmed from users "
          ." where login='$login_or_email' or email='$login_or_email'";
    $result = mysql_query($sql) or die(mysql_error());
    

    if (mysql_num_rows($result)==0){
        echo 'Пользователя с таким email или логином не найдено';    
    }

    $data=  mysql_fetch_array($result);
    list($email,$pwd,$user_id,$user_name,$email_confirmed) = $data;
    
    if ($email_confirmed){


    # нужно попасть в скрипт
    $link = LOCATION.'/?restore='.$user_id.'&hash='.$pwd;

    $message = 'Для изменения пароля перейдите по ссылке '
            .'<a href="'.$link.'">Изменение пароля</a>';
    mail($email, 'Восстановление пароля', $message);
    } else {
        echo 'К сожалению адрес электронной почты указаный пр регистрации не поддтерждён';
    }

}
/**
 * Восстановление пароля часть 2-я
 * Пользователь вошёл по ссылке из письма
 * @return type
 */
function restore(){

    $user_id = filter_input(INPUT_POST,'user_id');
    $hash = filter_input(INPUT_POST,'hash');
    $password1 = html_entity_decode(filter_input(INPUT_POST,'password1'));
    $password2 = html_entity_decode(filter_input(INPUT_POST,'password2'));

    if ($password1!==$password2){
        die('Пароли не совпадают');
        
    }

    $result = mysql_query("select user_id,concat(last_name,' ',first_name),role_id from users where user_id=$user_id and pwd='$hash'")
             or die(mysql_error());
    if (mysql_num_rows($result)!==1) {
        die('upss! user not found!?');
    }
    $data = mysql_fetch_array($result);
    $newpwd = md5($password1.TOPSICRET);
    mysql_query("update users set pwd='$newpwd',email_confirmed=true where user_id=".$data['user_id']) or die(mysql_error());
    list($_SESSION['user_id'],$_SESSION['user_name'],$_SESSION['role_id'])=$data;
}


function permission(){
    $p = new Permission(filter_input(INPUT_POST,'user_id'));
    $p->edit();
}

function permission_update(){
    $p= new Permission(filter_input(INPUT_POST,'user_id'));
    $p->update();
}

function delete_user(){
    $user_id = filter_input(INPUT_POST, 'user_id');
    if (mysql_query('delete from users where user_id='.$user_id)){
        return '{"error":'.ERROR_OK.'}';
    }
    return '{"error":'.ERROR_SQL.',"message":"'.mysql_error().'"}';
}


/**
 * Список пользователей
 * @return string
 */
function userlist(){

    $count=15;
    $page = filter_input(INPUT_POST,'page');
    if (!isset($page)){
        $page=1;
    }

    $result = mysql_query("select count(*) from users") or die(mysql_error());
    $data=  mysql_fetch_array($result);
    $usercount = $data[0];
    $page_count = intval(($data[0]-1)/$count)+1;


    $start = ($page-1)*$count;

    $result = mysql_query("select * from v_users order by user_id limit $start,$count") or die(mysql_error());
    $html = '';

    $html .= '<table>';
    $recno = 0;
    while ($data = mysql_fetch_array($result)){
        list($user_id,$user_name,$login,$role_name,$email,$confirmed,$reg_date,$last_visit,$visit_count)=$data;
        $recno++;
        $html .='<tr data-id="'.$user_id.'">';
        $html .="<td><input type='checkbox'></td><td>$role_name</td><td><a href='#' data-action='user'>$user_name</a></td><td>$login</td>"
            . "<td>$email</td><td>$reg_date</td><td>$last_visit</td><td>$visit_count</td>"
             ."<td><button data-action='delete'>Удалить</buttom></td><td><input type='checkbox' ".($confirmed?'checked':'')." disabled></td>";
        $html .='</tr>';
    }

    $html.='</table>';

    $first = $page>1?'<a href="#" data-page="1">1</a>...':'';
    $prior = $page>2?'<a href="#" data-page="'.($page-1).'" >'.($page-1).'</a>':'&nbsp';
    $next  = $page<$page_count-1?'<a href="#" data-page="'.($page+1).'">'.($page+1).'</a>':'&nbsp;';
    $last  = $page<$page_count?'...<a href="#" data-page="'.$page_count.'">'.$page_count.'</a>':'';

    $html.= '<div>всего пользователей <b>'.$usercount.'</b></div>';

    $html.= $first.$prior.'&nbsp;'.$page.'&nbsp'.$next.$last;

    return  '<div class="user-list-panel">'.$html.'</div>';
    
}

