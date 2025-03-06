<?php
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