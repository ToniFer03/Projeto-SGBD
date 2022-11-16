<?php
require_once "/opt/bitnami/apps/wordpress/custom/php/common.php";

//declaração de variáveis
$capability = 'manage_records';
$databaseip = 'localhost';
$username = 'root';
$password = 'sgbdc4';
$databaseName = 'bitnami_wordpress';
$errorMessage = "An error has occured";

//conectar a base de dados
$link = mysqli_connect($databaseip, $username, $password) or die($errorMessage);
mysqli_select_db($link, $databaseName);


if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_POST)) { //checks if post is empty
            $table = "child";
            $num_child = count_rows($link, $table);

            if($num_child == 0){
                print("Não há crianças!");
            } else {
                //debugging message
                print("Há crianças!");
            }
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


/* 
Para funções de queries necessário realizar os procedimentos a baixo
ter uma variável para a query
ter uma variável result que obtem o resultado da query
e ter uma variável row que busque cada linha do resultado acima
*/
function count_rows($connection, $table){
    //criação da query
    $query = "select count(1) from $table"; 

    //execução da query
    $result = mysqli_query($connection, $query);

    //processamento do resultado da query
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    return $row[0];
}
?>