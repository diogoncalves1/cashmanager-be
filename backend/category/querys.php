<?php

function get_categoryID_by_name($conn, $name)
{
    $query = "SELECT id FROM category WHERE name = '$name'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['id'];
}

function get_category_name($conn, $id)
{
    $query = "SELECT name, name_pt FROM category WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if ($_COOKIE['lang'] == "EN")
        return $row['name'];
    else
        return $row['name_pt'];
}

function update_category($conn, $code,  $PT, $EN, $sub_cat, $id)
{
    $query = "UPDATE category SET `sub-category`=$sub_cat WHERE id = $id";
    $conn->query($query);
    $url = "../backend/translate.xml";
    $xml = simplexml_load_file($url);

    foreach ($xml as $word) {
        if ($word->name == $code) {
            $word->PT = $PT;
            $word->EN = $EN;
        }
    }
    $xml->asXML($url);
}

function get_code_cat($conn, $id)
{
    $query = "SELECT code FROM category WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row["code"];
}

function get_all_category($conn, $id = 0)
{
    $query = "SELECT * FROM category WHERE ";
    if ($id != 0)
        $query .= "id=$id";
    else
        $query .= "1";
    $result_cats = $conn->query($query);
    return $result_cats;
}
