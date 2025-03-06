<?php

//GET
function get_limit($conn, $id = 0, $cat_id = 0, $user_id = 0, $type = -1)
{
    $date = new DateTime("now");

    $query = "SELECT * FROM limits WHERE";
    if ($id != 0)
        $query .= " id = $id";
    if ($cat_id != 0) {
        if ($id != 0)
            $query .= " AND";
        $query .= " cat_id = $cat_id";
    }
    if ($user_id != 0) {
        if ($id != 0 || $cat_id != 0)
            $query .= " AND";
        $query .= " user_id=$user_id";
    }
    if ($type != -1) {
        if ($id != 0 || $cat_id != 0 || $user_id != 0)
            $query .= " AND";
        $query .= " type=$type";
        switch ($type) {
            case 0: {
                    $date = $date->format("Y-m-d");
                    break;
                }
            case 1: {
                    $j = 0;
                    $i = 0;
                    while ($j != 1) {
                        $date = new DateTime("now");
                        $date = $date->modify("-$i days");
                        if ($date->format("D") == "Sun")
                            $j = 1;
                        $i++;
                    }
                    $date = $date->format("Y-m-d");
                    break;
                }
            case 2: {
                    $date = $date->format("Y-m-01");
                    break;
                }
            case 3: {
                    $date = $date->format("Y-01-01");
                    break;
                }
        }
    } else {
        $date = $date->format("Y-m-d");
    }

    $query .= " AND create_at >= '$date'";
    $result = $conn->query($query);
    if ($result->num_rows > 0)
        return $result;
}

function get_limit_type($conn, $id)
{
    $query = "SELECT type FROM limits WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['type'];
}

function get_limit_cat($conn, $id)
{
    $query = "SELECT cat_id FROM limits WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['cat_id'];
}

// INSERT
function insert_limit($conn, $max, $cat_id, $user_id, $type)
{
    $create_at = new DateTime("now");
    $create_at = $create_at->format("Y-m-d H:i:s");
    $query = "INSERT INTO limits (max, user_id, cat_id, create_at, type) VALUES ($max, $user_id, $cat_id, '$create_at', $type)";
    $conn->query($query);
}

// UPDATE
function update_limit($conn, $id, $max = 0, $current = 0)
{
    $query = "UPDATE limits SET";
    if ($max != 0) {
        $query .= " max=$max";
    }
    if ($current != 0) {
        if ($max != 0)
            $query .= " AND";
        $query .= " current=$current";
    }
    $query .= " WHERE id = $id";
    $conn->query($query);
}

// DELETE
function delete_limit($id)
{
    include("config.php");
    $cat_id = get_limit_cat($conn, $id);
    $type = get_limit_type($conn, $id);

    switch ($type) {
        case 0: {
                $class = " class='td-daily'";
                break;
            }
        case 1: {
                $class = " class='td-weekly'";
                break;
            }
        case 2: {
                $class = " class='td-monthly'";
                break;
            }
        case 3: {
                $class = " class='td-annual'";
                break;
            }
    }

    $query = "DELETE FROM limits WHERE id= $id";
    $conn->query($query);
    delete_alert($conn, 0, $cat_id);
    $response = "";
    $result_limits = get_limit($conn, 0, 0, $user_id, $type);
    if (isset($result_limits)) {
        while ($row = mysqli_fetch_assoc($result_limits)) {
            $response .= '<tr class="';
            if ($_COOKIE['mode'] == "light") {
                $response .= "table-primary";
            }
            $response .= 'primary">
                <td' . $class . '>' . get_category_name($conn, $row['cat_id']) . '</td>
                <td' . $class . '>' . $row['max'] . $coin . '</td>
                <td' . $class . '>' . $row['current'] . $coin . '</td>
                <td' . $class . '>';
            if ($row['max'] - $row['max'] * 0.2 > $row['current']) {
                $response .= '<svg class="bi" style="color:rgb(33, 141, 1);">
                            <use xlink:href="#emoji-smile" />
                        </svg>';
            } elseif ($row['max'] <= $row['current']) {
                $response .= '<svg class="bi" style="color:rgb(179, 26, 26);">
                            <use xlink:href="#emoji-frown" />
                        </svg>';
            } else {
                $response .= '<svg class="bi" style="color:rgb(224, 221, 0);">
                            <use xlink:href="#emoji-neutral" />
                        </svg>';
            }
            $response .= '</td>
                <td' . $class . '>
                    <button style="background: none; border: none;"
                        onclick="goToEdit(' . $row['id'] . ')"><svg class="bi">
                            <use xlink:href="#edit" />
                        </svg>
                    </button>
                </td>
                <td ' . $class . '>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                        style="background: none; border: none;"
                        onclick="modal(' . $row['id'] . ', ' . $row['type'] . ')"><svg class="bi">
                            <use xlink:href="#delete" />
                        </svg></button>
                </td>
            </tr>';
        }
    }
    echo $response;
}

function search_limits($conn, $values, $user_id, $categorys, $type)
{
    $maxInputs = count($values);
    for ($i = 0; $i < $maxInputs; $i++) {
        insert_limit($conn, $values[$i], $categorys[$i], $user_id, $type[$i]);
    }
}
