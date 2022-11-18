<?php
require_once "custom/php/common.php";

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
                $collums = array("Nome", "Data de nascimento", "Enc. de educação",
            "Telefone do Enc.", "e-mail", "registos");
            $orderColumn = "name";
                create_table($link, $collums, $table, $orderColumn);
            }
            //caso existam mostrar uma tabela com todas as crianças
            //ordenado por ordem alfabetica
            //após a tabela
            echo "<h3> Dados de registo - introdução </h3>";
            echo "<p> Introduza os dados pessoais básicos da criança: ";
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

//função para a criação da tabela
function create_table($connection, $collums, $table, $orderColumn){
    echo "<table>";
    
    //cria os titulos das colunas
    echo "<tr>";
    foreach ($collums as $value){
        echo "<td> $value </td>";
    };
    echo "</tr>";

    //criar todas as linhas da tabela com todos os valores
    $result = get_all_rows($connection, $table, $orderColumn);
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        echo "<tr> 
        <td>$row[1]</td>
        <td>$row[2]</td>
        <td>$row[3]</td>
        <td>$row[4]</td>
        <td>$row[5]</td>";
        $itemName = get_item($connection);

        echo "<td>";
        foreach($itemName as $value){
            $string = $value;
            $string = $string . " " . get_values_child($connection, $row[0], $value);
            echo $string;
        }
        echo "</td>
        </tr>";
    }

    echo"</table>";
}

//função que recebe todas as linhas de uma tabela, ordenada
function get_all_rows($connection, $table, $orderColumn){
    //criação da query
    $query = "Select * from $table ";
    $query = $query . "Order By $orderColumn";

    //execução da query
    $result = mysqli_query($connection, $query);
    return $result;
}

//função para obter todos os itens
//vai ser necessário alterar esta função para obter apenas os itens que a criança precisa
function get_item($connection){
    //variáveis a serem usadas nas querys
    $collum = "item.name";
    $table = "item";
    $order = "item.name";

    //criação da query
    $query = "Select " . $collum . " ";
    $query = $query . "From " . $table . " ";
    $query = $query . "Order by " . $order;

    //execução da query
    $result = mysqli_query($connection, $query);

    $contador = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $itemName[$contador] = $row[0];
        $contador = $contador + 1;
    }

    return $itemName;
}

//função que busca todos os valores das crianças
function get_values_child($connection, $child_wanted, $item_wanted){
    //variáveis a serem usadas nas queries
    $collums = array("value.value", "subitem.name");
    $tables = array("child, value, item, subitem");
    $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", 
    "subitem.item_id = item.id", "item.name Like");
    $order = "item.name";

    //criação da query
    $query = "Select Distinct ". implode(",", $collums) . " ";
    $query = $query . "From ". implode(",", $tables) . " ";
    $query = $query . "where " . implode(" and ", $conditions) . " ";
    $query = $query . '"' . $item_wanted . '" '; 
    $query = $query . "Order by $order";
    
    //execução da query
    $result = mysqli_query($connection, $query);
    
    $string = "";
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
            $string = $string . $row[1] . " ";
            $string = $string . "(" . $row[0] . "); ";
    }
    
    print $string;
    return $string;
}

?>