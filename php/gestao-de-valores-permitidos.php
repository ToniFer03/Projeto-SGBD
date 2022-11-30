<?php
require_once "custom/php/common.php";

//declaração de variáveis
$capability = 'manage_allowed_values';
$indicesFormulario = array("Valor");

if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_REQUEST)) { //checks if post and get are empty
            if(check_subitem_enum() > 0){
                //present the table in this case
                $collumHeaders = array("item", "id", "subitem", "id", "valores permitidos", "estado", "ação");
                create_table($collumHeaders);

            } else { //there are not enough items
                print "Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.";
            }
        } else {
            switch($_REQUEST["estado"]){
                case 'introducao':
                    $_SESSION["subitem_id"] = $_REQUEST["subitem"];
                    echo "<h3>Gestão de valores permitidos - introdução</h3>";
                    present_form($indicesFormulario);
                    break;
                case 'inserir':
                    $valor = testar_input($_REQUEST[$indicesFormulario[0]]);
                    echo "<h3>Gestão de valores permitidos - inserção</h3>";
                    insert_values($valor);
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

//function to create the table
function create_table($collums){
    echo "<table>";
    
    //cria os titulos das colunas
    echo "<tr>";
    foreach ($collums as $value){
        echo "<th> $value </th>";
    }
    echo "</tr>";

    //obteer todos os itens
    $itemArray = get_item_array();

    //preencher todas as linhas da tabela 
    foreach($itemArray as $value){
        $numberRowsItems = get_number_rows_item($value);
        echo "<tr>";
        echo "<td rowspan='$numberRowsItems'> $value </td>";
        get_subitem_array($value);
        echo "</tr>";
    }

    echo"</table>";
}

//checks if there are any subitems with enum as value_type
function check_subitem_enum(){
    //declarar variáveis
    $isDistinct = false;
    $collums = array('*');
    $tables = array("subitem_allowed_value", "subitem");
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'");

    $result = get_count_numbers($isDistinct, $collums, $tables, $conditions);

    return $result;
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

//função que busca items cujos subitems possuem tipos de valor enum
function get_item_array(){
    //declarar variáveis
    $isDistinct = true;
    $collums = array("item.name");
    $tables = array("subitem", "item");
    $conditions = array("item.id = subitem.item_id");
    $order = $collums[0];
    $itemArray = [];


    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    //recebe todas as linhas do query
    $i = 0;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $itemArray[$i++] = $row[0];
    }

    return $itemArray;
}

//function that presents the subitems in the tables
function get_subitem_array($itemName){
    //declarar variáveis
    $isDistinct = true;    
    $collums = array("subitem.name", "subitem.id");
    $tables = array("subitem", "item");
    $conditions = array("item.id = subitem.item_id", "item.name like '$itemName'");
    $order = $collums[1];

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    //retrives every row from the result an presents it on the table
    $firstPass = true;
    while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $numberRowsSubitems = get_number_rows_subitem($itemName, $row[1]);
        $getLink = $GLOBALS['current_page'] . "?estado=introducao&subitem=$row[1]";
        if($numberRowsSubitems == 0){
            if($firstPass){
                echo "<td> $row[1] </td>";
                echo "<td><a href='$getLink'>[$row[0]]</a></td>";
                echo "<td colspan='4'> Não há valores permitidos definidos</td>";;
                $firstPass = false;
            } else {
                echo "<tr>";
                echo "<td> $row[1] </td>";
                echo "<td><a href='$getLink'>[$row[0]]</a></td>";
                echo "<td colspan='4'> Não há valores permitidos definidos</td>";
                echo "</tr>";
            }
        } else {
            if($firstPass){
                echo "<td rowspan='$numberRowsSubitems'> $row[1] </td>";
                echo "<td rowspan='$numberRowsSubitems'><a href='$getLink'>[$row[0]]</a></td>";
                get_allowed_values_array($itemName, $row[1]);
                $firstPass = false;
            } else {
                echo "<tr>";
                echo "<td rowspan='$numberRowsSubitems'>$row[1] </td>";
                echo "<td rowspan='$numberRowsSubitems'><a href='$getLink'>[$row[0]]</a></td>";
                get_allowed_values_array($itemName, $row[1]);
                echo "</tr>";
            }
        }
    }
}

//functions that presents all the allowed values in the tables
function get_allowed_values_array($itemName, $subitemID){
    //declarar variáveis
    $isDistinct = true;
    $collums = array("subitem_allowed_value.id", "subitem_allowed_value.value", "subitem_allowed_value.state");
    $tables = array("subitem_allowed_value", "subitem", "item");
    $conditions = array("subitem_allowed_value.subitem_id = subitem.id", "subitem.value_type = 'enum'", "item.id = subitem.item_id", "item.name like '$itemName'", "subitem_allowed_value.subitem_id = $subitemID");
    $order = $collums[0];

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    //retrives every row from the result and presents it on the table
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

function present_form($indicesFormulario){
    echo "<form action='#' method='POST'>";

    //Valor
    echo "<label for=$indicesFormulario[0]><Strong>Inserir novo valor permitido:</Strong><em> - Valor</em></label><br>";
    echo "<input type='text' name='$indicesFormulario[0]'>";

    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<input type='submit' name='submit' value='submit'>";
}

function insert_values($valor){
    $query = "Insert into subitem_allowed_value ";
    $query = $query . "(subitem_id, value, state) ";
    $query = $query . "Values (" . $_SESSION['subitem_id'] . ", '$valor', 'active')";
    
    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);

    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } else {
        echo "Dados inseridos com sucesso!";
    }

    $temp = $GLOBALS['current_page'];
    echo "<p> ";
    echo "<em> Clique em <strong>Continuar</strong> para avançar</em><br>";
    echo "<a href=$temp> Continuar </a></p>";
}
?>