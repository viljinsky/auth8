<?php

    include_once 'config.php';
    
    mysql_connect(HOST,USER,PASSWORD) or die(mysql_error());
    mysql_select_db(DATABASE) or die(mysql_error());

