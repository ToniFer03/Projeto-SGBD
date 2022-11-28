<?php
require_once "custom/php/common.php";

//declaração de variáveis
$capability = 'manage_allowed_values';

if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_REQUEST)) { //checks if post and get are empty
            if(check_subitem_enum() > 0){
                //do stuff
            } else {
                print "Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.";
            }
        } else {

        }
    } else {
        print "Não têm autorização para aceder a esta página!";
        voltarAtras();
    }
} else {
    print "User is not logged in!";
}

//checks if there are any subitems with enum as value_type
function check_subitem_enum(){
    //declarar variáveis
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'");
    $tables = array("subitem_allowed_value", "subitem");

    //construção da query
    $query = "Select COUNT(*) From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    }

    $numeroEnums =  mysqli_fetch_array($result, MYSQLI_NUM);
    return $numeroEnums[0];
}
?>