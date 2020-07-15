<?php

$token = explode("=%20", $_SERVER['HTTP_REFERER']);
setcookie('Authorization',$token[1],time()+3600*1);
//echo "var token="."'$token[1]'";
?> 