<?php
try {
    $url_xml = "../backend/translate.xml";
    if (!file_exists($url_xml)) {
        throw new Exception("This file not exists");
    }
    $xml = simplexml_load_file($url_xml);
    // Verificar se o arquivo foi carregado corretamente
    if ($xml === false) {
        echo "Erro ao carregar o arquivo XML.";
        exit;
    }

    $lang = $_COOKIE['lang'];
    if (!is_admin($conn, $user_id)) {
        foreach ($xml->word as $palavra) {
            $words["{$palavra->name}"] = (string) $palavra->$lang;
        }
    } else {
        foreach ($xml->word as $palavra) {
            $words["EN"]["{$palavra->name}"] = (string) $palavra->EN;
            $words["PT"]["{$palavra->name}"] = (string) $palavra->PT;
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
