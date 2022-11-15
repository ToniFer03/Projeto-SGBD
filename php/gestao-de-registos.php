<?php
require_once "/opt/bitnami/apps/wordpress/custom/php/common.php";
$capability = 'Manage records';

if (is_user_logged_in()){
    if (current_user_can( $capability )){
        //comandos a serem efetuados
    } else {
        print "Não têm autorização para aceder a esta página!";
    }
} else {
    //Just a debbugin line
    print "user is not logged in";
}
?>