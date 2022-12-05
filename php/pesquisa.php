<?php
require_once "custom/php/common.php";
$capability = 'search';

if(is_user_logged_in()){
    if(current_user_can($capability)){
        if(empty($_REQUEST)){
            echo"<h3>Pesquisa - escolher itens</h3>";
            //apresentar lista de todos os itens permitidos
        } else {
            switch($_REQUEST["estado"]){
                case 'escolha':
                    $_SESSION["item_id"]; //recebe ids dos itens escolhidos
                    $_SESSION["item_name"]; //recebe nomes dos itens escolhidos
                    break;
                case 'escolher_filtros':
                    break;
                case 'execucao':
                    break;
                default:
                    print "Ocorreu um erro, valor de estado incorreto";
                    break;
            }
        }
    } else {
        print "Não têm autorização para aceder a esta página!";
        voltarAtras();
    }
} else {
    print "User is not logged in!";
}
?>