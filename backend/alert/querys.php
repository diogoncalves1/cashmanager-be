<?php

// GET
function get_alert($conn, $user_id) // FUNÇÃO QUE RETORNA O RESULT DA QUERY DOS ALERTS
{
    $date = new DateTime("now"); // PEGA A DATA ATUAL
    $date = $date->format("Y-m-01"); // FORMATA
    $query = "SELECT * FROM alert WHERE user_id = $user_id AND date >= '$date'"; // QUERY
    if ($_SESSION['page'] != "home")  // SE A PÁGINA ATUA NÃO FOR O "HOME" RETORNAR APENAS AS "NÃO LIDAS"
        $query .= " AND readed = 0"; // ATUALIZAÇÃO DA QUERY
    $result = $conn->query($query); // QUERY A DB
    return $result; // RETORNA O RESULT
} 

// SETS
function set_readed_alert($id) // FUNÇÃO QUE ATUALIZA O ESTADO DO ALERTA PARA "LIDO"
{
    include("config.php"); // INCLUI A DB
    $query = "UPDATE alert SET readed = 1 WHERE id = $id"; // QUERY
    $conn->query($query); // ATUALIZAÇÃO DA DB
}

// DELETE
function delete_alert($conn, $id = 0, $cat_id = 0) // FUNÇÃO QUE DELETA ALERTAS
{
    $date = new Datetime("now");
    $date = $date->format("Y-m-01");
    $query = "DELETE FROM alert WHERE date>='$date'";
    if ($id != 0)
        $query .= "AND id = $id"; // QUERY
    if ($cat_id != 0) {
        $query .= " AND cat_id = $cat_id";
    }
    $conn->query($query); // ATUALIZAÇÃO DA DB
}

// INSERT
function insert_alert($conn, $user_id, $cat_id, $type, $diff, $date)
{
    $coin = $_COOKIE['coin_symbol'];
    $cat_name = get_category_name($conn, $cat_id);
    $query = "SELECT * FROM alert WHERE cat_id = $cat_id AND date >= '$date'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        delete_alert($conn, $row['id']);
    }

    if ($type == 0) {
        $mensage = "Está a " . $diff . $coin . " de chegar ao seu limite da categoria " . $cat_name . "!";
    } else {
        $mensage = "Ultrapassou " . $diff . $coin . " do seu limite da categoria " . $cat_name . "!";
    }

    $query = "INSERT INTO alert (user_id, type, mensage, date, cat_id) VALUES ($user_id, $type, '$mensage', '$date', $cat_id)";
    $conn->query($query);
}

function check_alert($conn, $user_id, $cat_id = 0, $value = 0, $date = 0, $status = 0, $limit_id = 0) /* FUNÇÃO QUE VERIFICA SE APÓS A TRANSAÇÃO
SERÁ NECESSÁRIO CRIAR UM ALERTA */
{
    $dateNow = new DateTime("now"); // PEGA A DATA ATUAL
    $dateNow = $dateNow->format("Y-m-01"); // FORMATA
    if ($dateNow <= $date || $date == 0) { // SE A DATA ATUAL FOR MAIOR OU IGUAL Á DATA DA TRANSAÇÃO FAZER O CODIGO
        $result = get_limit($conn, $limit_id, $cat_id, $user_id, 1); // RESULTADO DOS LIMITES DA CATEGORIA

        if (isset($result) && $result->num_rows > 0) { // SE O RESULT RETORNADO TIVER ALGUMA LINHA FAZER O CODIGO
            $limit = $result->fetch_assoc(); // ARRAY
            if ($status == 0) // SE FOR UMA DESPESA GERADA
                $current = $limit['current'] += $value; // AUMENTA O ESTADO DO VALOR ATUAL GASTO
            else // SE FOR UMA DESPESA DELETA
                $current = $limit['current'] -= $value; // DIMINUI O ESTADO DO VALOR ATUAL GASTO
            update_limit($conn, $limit['id'], 0, $current); // ATUALIZAÇÃO DO LIMITE
            // DIFERENÇA ENTRE O LIMITE E ESTADO ATUAL DE GASTOS
            $limitWarning = $limit['max'] - $limit['max'] * 0.2; // TESTE SE JÁ ESTA PERTO DE ATINGIR O LIMITE
            if ($limit['current'] >= $limit['max']) { // SE O ESTADO ATUAL FOR MAIOR QUE O LIMITE
                $diff = $limit['current'] - $limit['max'];
                insert_alert($conn, $user_id, $limit["cat_id"], 1, $diff, $dateNow); /*
CRIA UM ALERTA VERMELHO A AVISAR QUE PASSOU x DO VALOR DO LIMITE*/
            } elseif ($limit['current'] >= $limitWarning) {
                $diff = $limit['max'] - $limit['current']; // SE ESTIVER A <= 20% DO LIMITE
                insert_alert($conn, $user_id, $limit['cat_id'], 0, $diff, $dateNow); /*
CRIA UM ALERTA AMARELO A AVISAR QUE FALTAM x PARA ANTIGIR O LIMITE */
            } else {
                delete_alert($conn, 0, $limit['cat_id']);
            }
        }
    }
}