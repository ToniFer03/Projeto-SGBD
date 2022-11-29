<?php
require_once "custom/php/common.php";

//declaração de variáveis
$capability = 'manage_allowed_values';

if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_REQUEST)) { //checks if post and get are empty
            if(check_subitem_enum() > 0){
                //present the table in this case
                $collums = array("item", "id", "subitem", "id", "valores permitidos", "estado", "ação");
                create_table($collums);

            } else { //there are not enough items
                print "Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.";
            }
        } else {
            //in case request has a stored value
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

//function to create a table
function create_table($collums){
    echo "<table>";
    
    //cria os titulos das colunas
    echo "<tr>";
    foreach ($collums as $value){
        echo "<th> $value </th>";
    }
    echo "</tr>";

    //preencher as linhas com a seguinte informação
    $itemArray = get_item_array();

    //test 
    foreach($itemArray as $value){
        $numberRowsItems = get_number_rows_item($value);
        echo "<tr>";
        echo "<td rowspan='$numberRowsItems'> $value </td>";
        get_subitem_array($value);
        echo "</tr>";
    }

    echo"</table>";
}

//função que busca items cujos subitems possuem tipos de valor enum
function get_item_array(){
    //declarar variáveis
    $collumsWanted = "item.name";
    $conditions = array("item.id = subitem.item_id");
    $tables = array("subitem", "item");
    $itemArray = [];

    //construção da query
    $query = "Select DISTINCT $collumsWanted "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);
    $query = $query . " Order by $collumsWanted";

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $itemArray[$i++] = $row[0];
    }

    return $itemArray;
}

/*
This function receives the indication if it is to be a distinct count or not, the tables to be included and the conditions and return a count of what we want based on that information
*/
function get_count_numbers($isDistinct, $collums, $tables, $conditions){
    //construção da query
    if($isDistinct){
        $query = "Select COUNT(DISTINCT " . implode(",", $collums). ") "; 
        $query = $query . "From " . implode(", ", $tables);
        $query = $query . " Where " . implode(" and ", $conditions);
    } else {
        $query = "Select COUNT(" . implode(",", $collums). ") "; 
        $query = $query . "From " . implode(", ", $tables);
        $query = $query . " Where " . implode(" and ", $conditions);
    }

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberCount = $row[0];
    }
    
    return $numberCount;
}

//function that returns how many rows should an item ocuppy
function get_number_rows_item($itemName){
    //gets the number of allowed values for any item
    $isDistinct = false;
    $collums = array('*');
    $tables = array("subitem_allowed_value", "subitem", "item");
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'");

    $result = get_count_numbers($isDistinct, $collums, $tables, $conditions);


    //gets the number of all subitems
    $isDistinct = true;
    $collums = array("subitem.id");
    $tables = array("subitem", "item");
    $conditions = array("item.id = subitem.item_id", "item.name like '$itemName'");

    $result = $result + get_count_numbers($isDistinct, $collums, $tables, $conditions);


    //get the number of subitems with allowed values
    $isDistinct = true;
    $collums = array("subitem.id");
    $tables = array("subitem_allowed_value", "subitem", "item");
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'");

    $result = $result - get_count_numbers($isDistinct, $collums, $tables, $conditions);

    return $result;
}

//function that return how many rows should a subitem ocuppy
function get_number_rows_subitem($itemName, $subitemID){
    //declarar variáveis
    $isDistinct = true;
    $collums = array("subitem_allowed_value.value");
    $tables = array("subitem_allowed_value", "subitem", "item");
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'", "subitem.id = $subitemID");

    $result = get_count_numbers($isDistinct, $collums, $tables, $conditions);

    return $result;
}

function get_subitem_array($itemName){
    //declarar variáveis
    $collumsWanted = "subitem.name, subitem.id";
    $conditions = array("item.id = subitem.item_id", "item.name like '$itemName'");
    $tables = array("subitem", "item");

    //construção da query
    $query = "Select DISTINCT $collumsWanted "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);
    $query = $query . " Order by subitem.name";

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $firstPass = true;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberRowsSubitems = get_number_rows_subitem($itemName, $row[1]);
        if($numberRowsSubitems == 0){
            if($firstPass){
                echo "<td> $row[1] </td>";
                echo "<td> $row[0] </td>";
                echo "<td colspan='4'> Não há valores permitidos definidos</td>";;
                $firstPass = false;
            } else {
                echo "<tr>";
                echo "<td> $row[1] </td>";
                echo "<td> $row[0] </td>";
                echo "<td colspan='4'> Não há valores permitidos definidos</td>";
                echo "</tr>";
            }
        } else {
            if($firstPass){
                echo "<td rowspan='$numberRowsSubitems'> $row[1] </td>";
                echo "<td rowspan='$numberRowsSubitems'> $row[0] </td>";
                get_allowed_values_array($itemName, $row[1]);
                $firstPass = false;
            } else {
                echo "<tr>";
                echo "<td rowspan='$numberRowsSubitems'> $row[1] </td>";
                echo "<td rowspan='$numberRowsSubitems'> $row[0] </td>";
                get_allowed_values_array($itemName, $row[1]);
                echo "</tr>";
            }
        }
    }
}

function get_allowed_values_array($itemName, $subitemID){
    //declarar variáveis
    $collumsWanted = "subitem_allowed_value.id, subitem_allowed_value.value, subitem_allowed_value.state";
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'", "subitem_allowed_value.subitem_id = $subitemID");
    $tables = array("subitem_allowed_value", "subitem", "item");

    //construção da query
    $query = "Select DISTINCT $collumsWanted "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $firstPass = true;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        if($firstPass){
            echo "<td> $row[0] </td>";
            echo "<td> $row[1] </td>";
            echo "<td> $row[2] </td>";
            echo "<td> place holder </td>";
            $firstPass = false;
        } else {
            echo "<tr>";
            echo "<td> $row[0] </td>";
            echo "<td> $row[1] </td>";
            echo "<td> $row[2] </td>";
            echo "<td> place holder </td>";
            echo "</tr>";
        }
    }
}
?>