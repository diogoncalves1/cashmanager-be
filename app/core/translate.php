<?php

use app\Models\UserModel;

try {
    $userInstance = new UserModel();
    $userId = $userInstance->id;

    $url_xml = "../app/core/translate.xml";
    if (!file_exists($url_xml)) {
        throw new Exception("This file not exists");
    }
    $xml = simplexml_load_file($url_xml);

    if ($xml === false) {
        echo "Erro ao carregar o arquivo XML.";
        exit;
    }

    $lang = $_COOKIE['lang'];
    if ($userId) {
        if (!is_admin($userId)) {
            foreach ($xml->word as $palavra) {
                $translate["{$palavra->name}"] = (string) $palavra->$lang;
            }
        } else {
            foreach ($xml->word as $palavra) {
                $translate["EN"]["{$palavra->name}"] = (string) $palavra->EN;
                $translate["PT"]["{$palavra->name}"] = (string) $palavra->PT;
            }
        }
    } else {
        foreach ($xml->word as $palavra) {
            $translate["{$palavra->name}"] = (string) $palavra->$lang;
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
