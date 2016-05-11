<?php

//------------------------------------------------------------------------------
//
//                  Настройки сервера базы данных
//
//------------------------------------------------------------------------------

define('TEST',true);

define('PROJECT','auth8');

if (!TEST){
    define('HOST', 'db03.hostline.ru');
    define('USER', 'vh237706_test');
    define('PASSWORD', 'test');
    define('DATABASE','vh237706_db1');
    
    /** Глобальны путь к сайту*/
    define('LOCATION','http://составительрасписания.рф/'.PROJECT);
} else {
    
    define('HOST', 'localhost');
    define('USER',PROJECT);
    define('PASSWORD', PROJECT);
    define('DATABASE',PROJECT);

    /** Глобальны путь к сайту*/
    define('LOCATION','http://localhost/'.PROJECT);
    
}

define('TOPSICRET','T0p3icret');


/** Путь к библиотеке auth */
define('ADMIN_PATH', LOCATION.'/libs/auth');

