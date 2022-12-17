<?php
require_once "custom/php/common.php";
$capability = 'search';

if(is_user_logged_in()){
    if(current_user_can($capability)){
        if(empty($_REQUEST)){
            echo"<h3>Pesquisa - escolher itens</h3>";
            showItemsList();
        } else {
            switch($_REQUEST["estado"]){
                case 'escolha':
                    $_SESSION["item_id"] = $_REQUEST["ite"];; 
                    $_SESSION["item_name"] = getItemName($_SESSION["item_id"]);
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

function showItemsList(){
    //apresentar lista de todos os tipos de itens com os seus respetivos itens
    echo "<ul> ";
    $itemTypes = getItemTypes();
    foreach($itemTypes as $value){
        echo "<li> $value </li>";
        echo "<ul>";
        $items = getItems($value);
        echo "</ul>";
    }
    echo "</ul>";
}

function getItemTypes(){
    //retorna array com os tipos de itens
    $isDistinct = false;
    $collums = array("name");
    $tables = array("item_type");
    $conditions = array("TRUE");
    $order = "(Select null)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $itemTypes = array();
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        array_push($itemTypes, $row[0]);
    }

    return $itemTypes;
}

function getItems($itemType){
    //retorna array com os itens de um tipo de item
    $isDistinct = TRUE;
    $collums = array("item.name", "item.id");
    $tables = array("item, item_type", "subitem");
    $conditions = array("item.item_type_id = item_type.id", "item_type.name = '$itemType'", "item.state = 'active'", "subitem.item_id = item.id");
    $order = "(Select null)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $getLink = $GLOBALS['current_page'] . "?estado=escolha&ite=$row[1]";
        echo "<li> <a href='$getLink'> [$row[0]] </a> </li>";
    }
}

function getItemName($itemID){
    //retorna o nome de um item
    $isDistinct = TRUE;
    $collums = array("item.name");
    $tables = array("item");
    $conditions = array("item.id = '$itemID'");
    $order = "(Select null)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    return $row[0];
}
?>