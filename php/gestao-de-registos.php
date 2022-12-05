<?php
require_once "custom/php/common.php";

//declaração de variáveis 
$capability = 'manage_records';
$indicesFormulario = array("child_name", "data_nascimento", "nome_encedu", "num_telefone", "email_tutor");
$collums_insert = array("name, birth_date, tutor_name, tutor_phone, tutor_email");
$valoresValidados = [];
$errosFormulario = [];

if (is_user_logged_in()){ //checks if the user is logged in
    if (current_user_can( $capability )){ //checks if the user has a specific capability
        if (empty($_POST)) { //checks if post is empty
            $table = "child";
            $num_child = count_rows($table);

            if($num_child == 0){
                print("Não há crianças!");
            } else {
                $collums = array("Nome", "Data de nascimento", "Enc. de educação", "Telefone do Enc.", "e-mail", "registos");
                $orderColumn = "name";
                create_table($collums, $table, $orderColumn);
            }
            //apos a tabela
            echo "<h3> Dados de registo - introdução </h3>";
            echo "<p> Introduza os dados pessoais básicos da criança: </p>";
            formulario_site($indicesFormulario);
        } else {
            switch ($_REQUEST["estado"]) {
                case 'validar':
                    echo "<h3> Dados de registo - validação </h3>";
                    validar_formulario($indicesFormulario, $valoresValidados, $errosFormulario);
                    
                    //caso exista algum erro no formulario mostrá-lo
                    if(!empty($errosFormulario)){
                    apresentar_erros($errosFormulario);
                    
                    //botão voltar atrás
                    voltarAtras();
                    } else {
                        echo "<p>";
                        echo "Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão corretos e pretende submeter os mesmos? <br>";
                        apresentar_validacao($valoresValidados, $indicesFormulario);
                    }
                    break;
                case 'inserir':
                    echo "<h3> Dados de registo - inserção </h3>";
                    inserir_dados("child", $indicesFormulario, $collums_insert);
                    break;
                default:
                    echo $errorMessage;
                    voltarAtras();
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


function count_rows($table){
    $isDistinct = false;
    $collum = array('*');
    $table = array($table);
    $conditions = array("TRUE"); //Não existem condições, logo usado o true

    $result = get_count_numbers($isDistinct, $collum, $table, $conditions);

    return $result;
}

//função para a criação da tabela
function create_table($collums, $table, $orderColumn){
    echo "<table>";
    
    //cria os titulos das colunas
    echo "<tr>";
    foreach ($collums as $value){
        echo "<td> $value </td>";
    };
    echo "</tr>";

    //criar todas as linhas da tabela com todos os valores
    $result = get_all_rows($table, $orderColumn);
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        echo "<tr> 
        <td>$row[1]</td>
        <td>$row[2]</td>
        <td>$row[3]</td>
        <td>$row[4]</td>
        <td>$row[5]</td>";
        $itemName = get_item($row[0]);

        echo "<td>";
        if($itemName[0] != 0){
            foreach($itemName as $value){
                $temp = strtoupper($value);
                echo "$temp: <br/>"; 
                $value_information = get_values_info($row[0], $value);
                foreach($value_information as $info){
                    if(is_null($info[1])){
                        $info[1] = "Null";
                    }
                    echo "$info[0] ($info[1]) - ";
                    $subitemName = get_subitem_names($row[0], $value, $info[0], $info[1]); 
                    foreach($subitemName as $nome_subitem){
                        $subitemValue = get_values_subitems($row[0], $nome_subitem, $value, $info[0], $info[1]);
                        echo "<strong>$nome_subitem</strong> (";
                        echo implode(", ",$subitemValue);
                        echo "); ";
                    }   
                    echo "<br/>";
                }
            }
        }
        echo "</td>
        </tr>";
    }

    echo"</table>";
}

//função que recebe todas as linhas de uma tabela, ordenada
function get_all_rows($table, $orderColumn){
    $isDistinct = false;
    $collums = array('*');
    $tables = array($table);
    $conditions = array("TRUE");
    $order = $orderColumn;

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    return $result;
}

//função para obter todos os itens
function get_item($child_wanted){
    //variáveis a serem usadas nas querys
    $isDistinct = true;
    $collums = array("item.name");
    $tables = array("child, value, item, subitem");
    $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id");
    $order = "item.name";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $i = 0;
    $itemName[0] = 0; 
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $itemName[$i++] = $row[0];
    }

    return $itemName;
}

//função que busca todos os subitems pertencentes a um item de uma dada criança
function get_subitem_names($child_wanted, $item_wanted, $date_wanted, $producer_wanted){
    //variáveis a serem usadas nas queries
    $isDistinct = true;
    $collums = array("subitem.name");
    $tables = array("child", "value", "item", "subitem");

    //in case the producer is null we need to use the IS NULL operator
    if($producer_wanted == 'Null'){
        $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id", "item.name = '$item_wanted'", "value.date = '$date_wanted'", "value.producer IS NULL");
    } else {
        $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id", "item.name = '$item_wanted'", "value.date = '$date_wanted'", "value.producer = '$producer_wanted'");
    }
    $order = "(Select NULL)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $subitemArray[$i++] = $row[0];
    }

    return $subitemArray;
}

function get_values_info($child_wanted, $item_wanted){
    //variáveis a serem usadas nas queries
    $isDistinct = true;
    $collums = array("value.date", "value.producer");
    $tables = array("child", "value", "item", "subitem");
    $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id", "item.name Like '$item_wanted'");
    $order = "(Select NULL)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        $j = 0;
        $temp[$j++] = $row[0];
        $temp[$j++] = $row[1];
        $dateArray[$i++] = $temp;
    }

    return $dateArray;
}

//função que busca todos os valores dos subitems obtidos de outra funçao
function get_values_subitems($child_wanted, $subItemWanted, $item_wanted, $date_wanted, $producer_wanted){
    //variáveis a serem usadas nas queries
    $isDistinct = true;
    $collums = array("value.value");
    $tables = array("child", "value", "item", "subitem");
    //in case the producer is null we need to use the IS NULL operator
    if($producer_wanted == 'Null'){
        $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id", "item.name Like '$item_wanted'", "subitem.name Like '$subItemWanted'", "value.date = '$date_wanted'", "value.producer IS NULL");
    } else {
        $conditions = array("$child_wanted = value.child_id", "value.subitem_id = subitem.id", "subitem.item_id = item.id", "item.name Like '$item_wanted'", "subitem.name Like '$subItemWanted'", "value.date = '$date_wanted'", "value.producer = '$producer_wanted'");
    }
    $order = "(Select NULL)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $unittype = get_unit_type($subItemWanted);

    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)){
        if(($unittype != "nenhum") && (is_numeric($row[0]))){
            $value_subitem_array[$i++] = $row[0] . " $unittype";
        } else {
            $value_subitem_array[$i++] = $row[0];
        }
    }
    
    return $value_subitem_array;
}

function get_unit_type($subitem){
    $isDistinct = false;
    $collums = array("subitem_unit_type.name");
    $tables = array("subitem", "subitem_unit_type");
    $conditions = array("subitem.name = '$subitem'", "subitem.unit_type_id = subitem_unit_type.id");
    $order = "(Select NULL)";

    $result = get_select_query($isDistinct, $collums, $tables, $conditions, $order);

    $row = mysqli_fetch_array($result, MYSQLI_NUM);

    if(is_null($row)){
        $unittype = "nenhum";
    } else {
        $unittype = $row[0];
    }

    return $unittype;
}

function formulario_site($indicesFormulario){
    echo "<form action='#' method='POST'>";

    //Nome da Criança
    echo "<label for=$indicesFormulario[0]><Strong>Nome da criança:</Strong><em> - Nome completo (*Campo obrigatório)</em></label><br>";
    echo "<input type='text' maxlength='128' name='$indicesFormulario[0]'>";

    //Data de nascimento
    echo "<label for=$indicesFormulario[1]><Strong>Data de nascimento:</Strong><em> - Formato (AAAA-MM-DD) (*Campo obrigatório)</em></label><br>";
    echo "<input type='text' name='$indicesFormulario[1]'>";

    //nome encarregado de educação
    echo "<label for=$indicesFormulario[2]><Strong>Nome do Enc. Educação:</Strong><em> - Nome completo (*Campo obrigatório)</em></label><br>";
    echo "<input type='text' maxlength='128' name='$indicesFormulario[2]'>";

    //telefone encarregado de educação
    echo "<label for=$indicesFormulario[3]><Strong>Telefone Enc. Educação:</Strong><em> - Formato (9 digitos, sem indicativo) (*Campo Obrigatório)</em></label><br>";
    echo "<input type='text' name='$indicesFormulario[3]'>";

    //email encarregado de educação
    echo "<label for=$indicesFormulario[4]><Strong>Email Enc. Educação:</Strong></label><br>";
    echo "<input type='text' name='$indicesFormulario[4]'>";

    echo "<input type='hidden' name='estado' value='validar'>";
    echo "<input type='submit' name='submit' value='submit'>";
}

function validar_formulario($indicesFormulario, &$valoresValidados, &$errosFormulario){

    if(empty($_REQUEST[$indicesFormulario[0]])) {
        $errosFormulario[$indicesFormulario[0]] = "Nome é obrigatório! Por favor preencha o nome da criança.";
    } else {
            $valoresValidados[$indicesFormulario[0]] = testar_input($_REQUEST[$indicesFormulario[0]]);

            //fazer check do nome
            if (preg_match('/^[a-zA-Z\x{00C0}-\x{00ff}\s]+$/u', $valoresValidados[$indicesFormulario[0]]) == 0) {
                $errosFormulario[$indicesFormulario[0]] = "Inseriu caracteres inválidos no nome da criança! Por favor corrija.";
             }
    }

    //validação da data de nascimento
    if(empty($_REQUEST[$indicesFormulario[1]])) {
            $errosFormulario[$indicesFormulario[1]] = "Data de nascimento é obrigatória! Por favor preencha a data de nascimento da criança.";
        } else {
            $valoresValidados[$indicesFormulario[1]] = testar_input($_REQUEST[$indicesFormulario[1]]);

            //fazer check do email
            if (!preg_match("/^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$/", $valoresValidados[$indicesFormulario[1]])) {
                $errosFormulario[$indicesFormulario[1]] = "Data de nascimento em formato inválido! Formato requisita é AAAA-MM-DD";
            }
    }

    //validação do nome do encarregado de educação
    if(empty($_REQUEST[$indicesFormulario[2]])) {
        $errosFormulario[$indicesFormulario[2]] = "Nome do Encarregado de Educação é obrigatório! Por favor insira o nome do Encarregado de Educação.";
    } else {
        $valoresValidados[$indicesFormulario[2]] = testar_input($_REQUEST[$indicesFormulario[2]]);

        //fazer o check do nome do encarregado de educação
        if (!preg_match('/^[a-zA-Z\x{00C0}-\x{00ff}\s]+$/u', $valoresValidados[$indicesFormulario[2]])) {
            $errosFormulario[$indicesFormulario[2]] = "Inseriu caracteres inválidos no Nome do Encarregado de Educação! Por favor corrija.";
        }
    }

    if(empty($_REQUEST[$indicesFormulario[3]])) {
        $errosFormulario[$indicesFormulario[3]] = "Numéro de telefone do Encarregado de educação é obrigatório!";
    } else {
        $valoresValidados[$indicesFormulario[3]] = testar_input($_REQUEST[$indicesFormulario[3]]);

        //fazer o check para o número de telefone
        if (!preg_match("/^\d{9}$/", $valoresValidados     [$indicesFormulario[3]])) {
            $errosFormulario[$indicesFormulario[3]] = "Inseriu um número de telemovel com formato inválido! Necessários 9 digitos.";
        }
    }
    
    $valoresValidados[$indicesFormulario[4]] = testar_input($_REQUEST[$indicesFormulario[4]]);

    //validar o email
    if(!filter_var($valoresValidados[$indicesFormulario[4]], FILTER_VALIDATE_EMAIL)){
        if(!$valoresValidados[$indicesFormulario[4]] == "")
            $errosFormulario[$indicesFormulario[4]] = "Inseriu um email inválido! Por favor corrija.";
    }

}

function apresentar_erros($errosFormulario){
    echo "<p>";
    foreach($errosFormulario as $valor){
        echo $valor . "<br>";
    }
    echo "</p>";
}

function apresentar_validacao($valoresValidados, $indicesFormulario){
    echo "Nome: " . $valoresValidados[$indicesFormulario[0]] . "<br>";
    echo "Data de Nascimento: " . $valoresValidados[$indicesFormulario[1]] . "<br>";
    echo "Nome Encarregado de Educação: " . $valoresValidados[$indicesFormulario[2]] . "<br>";
    echo "Número de Telefone: " . $valoresValidados[$indicesFormulario[3]] . "<br>";
    echo "Email: " . $valoresValidados[$indicesFormulario[4]]  . "<br>";
    echo "</p>";

    $i = 0;
    foreach($valoresValidados as $valor){
        $valoresValidados[$i] = $valoresValidados[$indicesFormulario[$i]];
        $i = $i + 1;
    }
    

    echo "<form action'#' method='POST'>";
    echo "<input type='hidden' name='$indicesFormulario[0]' value='$valoresValidados[0]'>";
    echo "<input type='hidden' name='$indicesFormulario[1]' value='$valoresValidados[1]'>";
    echo "<input type='hidden' name='$indicesFormulario[2]' value='$valoresValidados[2]'>";
    echo "<input type='hidden' name='$indicesFormulario[3]' value='$valoresValidados[3]'>";
    echo "<input type='hidden' name='$indicesFormulario[4]' value='$valoresValidados[4]'>";
    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<input type='submit' name='submit' value='submit'>";
}

function inserir_dados($table, $indicesFormulario, $collums_insert){
    //criação da query
    $query = "Insert into $table ";

    $query = $query . "(" . implode(" , ", $collums_insert) . ") ";

    $query = $query . " Values (";
    $query = $query . "'". $_REQUEST[$indicesFormulario[0]] .  "' ";
    $query = $query . ", " . "'". $_REQUEST[$indicesFormulario[1]] .  "' ";
    $query = $query . ", " . "'". $_REQUEST[$indicesFormulario[2]] .  "' ";
    $query = $query . ", " . $_REQUEST[$indicesFormulario[3]] .  " ";
    $query = $query . ", " . "'". $_REQUEST[$indicesFormulario[4]] .  "' ";
    $query = $query . " )";

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