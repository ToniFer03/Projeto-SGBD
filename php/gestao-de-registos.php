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
            "Telefone do Enc.", "e-mail");
            $orderColumn = "name";
                create_table($link, $collums, $table, $orderColumn);
                //get_values_child($link);
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
        <td>$row[5]</td>
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

//função que busca todos os valores das crianças
function get_values_child($connection){
    //example of the query line to be used
    //SELECT * FROM value, item, subitem, child where child.id = value.child_id and value.subitem_id = subitem.id and subitem.item_id = item.id;
    //query = "Select * from value, child where value.child_id = child.id";
    $result = mysqli_query($connection, $query);
    
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){

    }
}

?>