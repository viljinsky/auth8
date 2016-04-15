<?php

define('TOPSICRET','T0p3icret');
//define('TOPSICRET', 'Vh1MTV100');


/**      Путь к папке auth  
 *      LOACTION - глобальная костанта в софиг
 **/
$admin_path = LOCATION.'/libs/auth';



//--------------- config -----------------------------

define ('ROLE_GUEST', 1);
define ('ROLE_USER' , 2);
define ('ROLE_ADMIN', 3);




/** Ошибок нет*/
define('ERROR_OK',0);
/** Неизвестная ошибка - не использовать*/
define('ERROR_NOT_FOUND',1);

/** Неизвестная ошибка */
define('ERROR_UNKNOW', 1000);


/**Пользователя с таким логином или адресом почты не найдено*/
define('ERROR_USER_NOT_FOUND',2);
/**Пользователь с таким логином или адресом почты уже существует*/
define('ERROR_USER_EXISTS',3);
/**Пароль или логин введён неверно*/
define('ERROR_BAD_PASSWORD',4);
/**Введено неверное число с картинки*/
define('ERROR_CAPTCHA',5);
/**Не подтверждён емейл пользователя*/
define('ERROR_NOT_CONFIRMED',37);

/**Ошибка при выполнении запроса*/
define('ERROR_SQL',33);
/** Ошибка при отправке сообщения */
define('ERROR_EMAIL',45);


//--------------- config -----------------------------

