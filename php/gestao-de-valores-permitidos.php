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
        $numberRowsItems = $numberRowsItems + get_number_subitems($value);
        echo "<tr>";
        echo "<td rowspan='$numberRowsItems'> $value </td>";
        get_subitem_array($value);
        echo "</tr>";
    }
        /*
    echo "<tr>";
    echo "<td rowspan='$numberRowsItems'> Austismo </td>";
    echo "<td> 3 </td>";
    echo "<td> Grau </td>";
    echo "<td> 4 </td>";
    echo "<td>  ligeiro </td>";
    echo "<td> ativo </td>";
    echo "<td> editar </td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td> 4 </td>";
    echo "<td> Grau </td>";
    echo "<td> 6 </td>";
    echo "<td>  ligeiro </td>";
    echo "<td> ativo </td>";
    echo "<td> editar </td>";
    echo "</tr>";
    */

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

function get_number_rows_item($itemName){
    //declarar variáveis
    $collumsWanted = "subitem_allowed_value.value";
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'");
    $tables = array("subitem_allowed_value", "subitem", "item");

    //construção da query
    $query = "Select COUNT($collumsWanted) "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);
    $query = $query . " Order by subitem.name";

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberRows = $row[0];
    }

    //for the subitems that dont have allowed values

    return $numberRows;
}

function get_number_rows_subitem($itemName, $subitemID){
    //declarar variáveis
    $collumsWanted = "subitem_allowed_value.value";
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'", "subitem.id = $subitemID");
    $tables = array("subitem_allowed_value", "subitem", "item");

    //construção da query
    $query = "Select COUNT(DISTINCT $collumsWanted) "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);


    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberRows = $row[0];
    }

    return $numberRows;
}

function get_number_subitems($itemName){
    //declarar variáveis
    $collumsWanted = "subitem.id";
    $conditions = array("item.id = subitem.item_id", "item.name like '$itemName'");
    $tables = array("subitem", "item");

    //construção da query
    $query = "Select COUNT(DISTINCT $collumsWanted) "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);


    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberRows = $row[0];
    }

    $number = $numberRows - temp_subitems_func($itemName);

    return $number;
}

function temp_subitems_func($itemName){
    //declarar variáveis
    $collumsWanted = "subitem.id";
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'");
    $tables = array("subitem_allowed_value", "subitem", "item");

    //construção da query
    $query = "Select COUNT(DISTINCT $collumsWanted) "; 
    $query = $query . "From " . implode(", ", $tables);
    $query = $query . " Where " . implode(" and ", $conditions);


    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $number = $row[0];
    }

    return $number;
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