<?php
require_once "custom/php/common.php";

//declaração de variáveis 
$capability = 'manage_records';
$databaseip = 'localhost';
$username = 'root';                         
$password = 'sgbdc4';
$databaseName = 'bitnami_wordpress';
$errorMessage = "An error has occured";
$indicesFormulario = [];
$indicesFormulario[0] = "child_name";
$indicesFormulario[1] = "data_nascimento";
$indicesFormulario[2] = "nome_encedu";
$indicesFormulario[3] = "num_telefone";
$indicesFormulario[4] = "email_tutor";
$valoresValidados = [];

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
            //apos a tabela
            echo "<h3> Dados de registo - introdução </h3>";
            echo "<p> Introduza os dados pessoais básicos da criança: </p>";
            formulario_site($indicesFormulario);
        } else {
            if($_POST["estado"] == "validar"){
                echo "<h3> Dados de registo - validação </h3>";
                $valoresValidados = validar_formulario($indicesFormulario);
            } else {
                //other code
            }
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
        $itemName = get_item($connection, $row[0]);

        echo "<td>";
        if($itemName[0] != 0){
            foreach($itemName as $value){
            $string = $value . ":";
            $string = $string . " " . get_values_child($connection, $row[0], $value);
            $string = ucfirst($string);
            echo "$string <br/>";
            }
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
function get_item($connection, $child_wanted){
    //variáveis a serem usadas nas querys
    $collum = "item.name";
    $tables = array("child, value, item, subitem");
    $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", 
    "subitem.item_id = item.id");
    $order = "item.name";

    //criação da query
    $query = "Select Distinct " . $collum . " ";
    $query = $query . "From ". implode(",", $tables) . " ";
    $query = $query . "where " . implode(" and ", $conditions) . " ";
    $query = $query . "Order by " . $order;

    //execução da query
    $result = mysqli_query($connection, $query);

    $itemName[0] = 0;

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
    
    return $string;
}

function formulario_site($indicesFormulario){
    echo "<form action'#' method='POST'>";
    //Nome da Criança
    echo "Nome Completo: <input type='text' maxlength='128' pattern='[a-zA-Z\u00C0-\u00ff ]+' name='$indicesFormulario[0]' required>";

    //Data de nascimento
    echo "Data de Nascimento: <input type='text' patter='[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' name='$indicesFormulario[1]' required>";

    //nome encarregado de educação
    echo "Nome Enc. Educação: <input type='text' maxlength='128' pattern='[a-zA-Z\u00C0-\u00ff ]+' name='$indicesFormulario[2]' required>";

    //telefone encarregado de educação
    echo "Telefone Enc. Edu: <input type='text' pattern='[0-9]{9}' name='$indicesFormulario[3]' required>";

    //email encarregado de educação
    echo "Email do tutor: <input type='text' pattern='[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$' name='$indicesFormulario[4]'>";

    echo "<input type='hidden' name='estado' value='validar'>";
    echo "<input type='submit' name='submit' value='submit'>";
}

function testar_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validar_formulario($indicesFormulario){
    $valorFormulario = [];

    if(empty($_POST[$indicesFormulario[0]])) {
        $nameErr = "Nome é obrigatório!";
    } else {
        $valorFormulario[$indicesFormulario[0]] = testar_input($_POST[$indicesFormulario[0]]);
    }

    if(empty($_POST[$indicesFormulario[1]])) {
            $data_nasc_Err = "Data de nascimento é obrigatória!";
        } else {
            $valorFormulario[$indicesFormulario[1]] = testar_input($_POST[$indicesFormulario[1]]);
    }

    if(empty($_POST[$indicesFormulario[2]])) {
        $nome_encedu_Err = "Nome do Encarregado de Educação é obrigatório!";
    } else {
        $valorFormulario[$indicesFormulario[2]] = testar_input($_POST[$indicesFormulario[2]]);
    }

    if(empty($_POST[$indicesFormulario[3]])) {
        $telErr = "Numéro de telefone do Encarregado de educação é obrigatório!";
    } else {
        $num_telefone[$indicesFormulario[3]] = testar_input($_POST[$indicesFormulario[3]]);
    }

    if(empty($_POST[$indicesFormulario[4]])) {
        $emailErr = "Email do Encarregado do Educação é obrigatório!";
    } else {
        $email_tutor[$indicesFormulario[4]] = testar_input($_POST[$indicesFormulario[4]]);
    }
}

?>