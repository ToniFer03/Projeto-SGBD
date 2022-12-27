<?php
require_once "custom/php/common.php";
$capability = 'search';
$child_collums = array("id", "name", "birth_date", "tutor_name", "tutor_phone", "tutor_email");

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
                    showTables($child_collums);
                    break;
                case 'escolher_filtros':
                    foreach($_REQUEST["obter"] as $value){
                        echo $value;
                    }
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

function showTables($child_collums){
    $i = 0;
    echo "<form action='#' method='POST'>";
    //show beggining of table 1
    echo "<table>";
    echo "<tr>";
    echo "<th> Atributos </th>";
    echo "<th> Obter </th>";	
    echo "<th> Filtro </th>";
    echo "</tr>";

    //show content of table 1
    foreach($child_collums as $value){
        echo "<tr>";
        echo "<td> $value </td>";
        echo "<td> <input type='checkbox' name='obter[]' value='$i'> </td>";
        echo "<td> <input type='checkbox' name='filtro[]' value='$i'> </td>";
        echo "</tr>";
        $i++;
    }

    echo "</table>";

    //show beggining of table 2
    echo "<table>";
    echo "<tr>";
    echo "<th> Subitem </th>";
    echo "<th> Obter </th>";
    echo "<th> Filtro </th>";
    echo "</tr>";

    //show content of table 2
    $subitems = getSubitems($_SESSION["item_id"]);
    foreach($subitems as $value){
        echo "<tr>";
        echo "<td> $value </td>";
        echo "<td> <input type='checkbox' name='obter[]' value='$i'> </td>";
        echo "<td> <input type='checkbox' name='filtro[]' value='$i'> </td>";
        echo "</tr>";
        $i++;
    }

    echo "</table>";
    echo "<input type='hidden' name='estado' value='escolher_filtros'>";
    echo "<input type='submit' name='submit' value='submit'>";

}

function getSubitems($itemID){
    //retorna array com os subitens de um item
    $isDistinct = TRUE;
    $collums = array("subitem.name");
    $tables = array("subitem");
    $conditions = array("subitem.item_id = '$itemID'");
    $order = "(Select null)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $subitems = array();
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        array_push($subitems, $row[0]);
    }

    return $subitems;
}

?>