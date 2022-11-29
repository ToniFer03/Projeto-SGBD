<?php

//declaração de variaveis
$clientsideval = 0;
$errorMessage = "An error has occured";

global $current_page;
$current_page = get_site_url() . '/' . basename(get_permalink());

global $link;
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die($errorMessage);

function voltarAtras(){ //função para voltar atrás
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
    <noscript>
    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
    </noscript>";
}

function get_enum_values($connection, $table, $column){ //função para buscar o número de linhas a construir
    $query = " SHOW COLUMNS FROM `$table` LIKE '$column' ";
    $result = mysqli_query($connection, $query );
    $row = mysqli_fetch_array($result , MYSQLI_NUM );
    #extract the values
    #the values are enclosed in single quotes
    #and separated by commas
    $regex = "/'(.*?)'/";
    preg_match_all( $regex , $row[1], $enum_array );
    $enum_fields = $enum_array[1];
    return( $enum_fields );   
}

/* This function receives the indication if it is to be a distinct count or not, the collums, the tables to be included and the conditions and return a count of what we want based on that information. Notes: In case there are no conditions to be used, simply pass an array with the string TRUE, DISTINCT can´t be used with an (*) in that case pass for example 1 in place of the asterisc */
function get_count_numbers($isDistinct, $collums, $tables, $conditions){
    //construção da query
    if($isDistinct){
        $query = "Select COUNT(DISTINCT " . implode(",", $collums). ") "; 
        $query = $query . "From " . implode(", ", $tables) . " ";
        $query = $query . "Where " . implode(" and ", $conditions) . " ";
    } else {
        $query = "Select COUNT(" . implode(",", $collums). ") "; 
        $query = $query . "From " . implode(", ", $tables) . " ";
        $query = $query . "Where " . implode(" and ", $conditions) . " ";
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


/* This function receives the indication if is to be a distinct select or not, the tables to be included, the conditions of the query, and the order to present the results. The purpose of this function is to serve as a base for all select queries and return it to be processed by another function Note: in case order by is not to be used pass the string (SELECT NULL)*/
function get_select_query($isDistinct, $collums, $tables, $conditions, $order){
    //construção da query
    if($isDistinct){
        $query = "Select DISTINCT " . implode(",", $collums) . " "; 
        $query = $query . "From " . implode(", ", $tables) . " ";
        $query = $query . "Where " . implode(" and ", $conditions) . " ";
        $query = $query . "Order by " . $order;
    } else {
        $query = "Select " . implode(",", $collums) . " "; 
        $query = $query . "From " . implode(", ", $tables) . " ";
        $query = $query . "Where " . implode(" and ", $conditions) . " ";
        $query = $query . "Order by " . $order;
    }

    //execução da query
    $result = mysqli_query($GLOBALS['link'], $query);
    
    if(!$result) {
        die ("O query falhou: " . mysqli_error($GLOBALS['link']));
    } 
    
    return $result;
}

?>