<?php
require_once "/opt/bitnami/apps/wordpress/custom/php/common.php";

//declaração de variáveis
$capability = 'manage_records';

if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_POST)) { //checks if post is empty
            //verificar se existem tuplos na tabela child
            //Mensagem de print <não há crianças>
            //caso existam mostrar uma tabela com todas as crianças
            //ordenado por ordem alfabetica

            //após a tabela
            echo "<h3> Dados de registo - introdução </h3>";
        }
    } else {
        print "Não têm autorização para aceder a esta página!";
    }
} else {
    //Just a debbugin line
    print "user is not logged in";
}
?>