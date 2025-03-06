<?php
require_once("category/querys.php");
require_once("objective/querys.php");
require_once("transactions/querys.php");
require_once("users/querys.php");
require_once("alert/querys.php");
require_once("limits/querys.php");
require_once("financial_goal/querys.php");
require_once("status_goal/querys.php");
require_once("friend/querys.php");
require_once("share_type/querys.php");
require_once("share_request/querys.php");
require_once("shares/querys.php");
require_once("scheduled_expenses/querys.php");
require_once("alert_scheduled/querys.php");
require_once("alert_user/querys.php");
require_once("debt/querys.php");
include("config.php");

// EXTRAS

function hasPermission($conn, $role_id, $permission_name)
{
    $query = "SELECT *
    FROM permission p 
    JOIN role_permission rp 
    ON p.id = rp.permission_id
    WHERE rp.role_id = $role_id 
    AND p.name = '$permission_name';";
    $result = $conn->query($query);
    if ($result->num_rows > 0)
        return 1;
    return 0;
}

function porcent($now, $meta)
{
    $porcent = ($now / $meta) * 100;
    $porcent = number_format($porcent, 2);
    return $porcent;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['function'])) {
        $funcName = $_POST['function'];
        switch ($funcName) {
            case "delete_objetive":
                echo delete_objetive($_POST['id']);
                break;
            case "set_mode":
                set_mode($_POST['mode']);
                break;
            case "set_readed_alert": {
                    set_readed_alert($_POST['id']);
                    break;
                }
            case "delete_limit": {
                    echo delete_limit($_POST['id']);
                    break;
                }
            case "delete_financial_goal": {
                    echo delete_financial_goal($_POST['id']);
                    break;
                }
            case "search_user": {
                    echo search_user($_POST['id']);
                    break;
                }
            case "send_fried_request": {
                    send_fried_request($_POST['id'], $_POST['type']);
                    break;
                }
            case "set_friend_request": {
                    echo set_friend_request($_POST['id'], $_POST['confirm']);
                    break;
                }
            case "remove_friend": {
                    echo delete_friend($_POST['id']);
                    break;
                }
            case "test_obj": {
                    echo test_obj($_POST['type'], $_POST['user_sent']);
                    break;
                }
            case "accept_request": {
                    accept_request($_POST['type'], $_POST['obj_id'], $_POST['id'], $_POST["role_id"]);
                    break;
                }
            case "delete_share_request": {
                    delete_share_request($conn, $_POST['id']);
                    break;
                }
            case "delete_share_obj": {
                    delete_share_obj($conn, $_POST['obj_id'], $_POST['user_id']);
                    break;
                }
            case "delete_share_f_goal": {
                    delete_share_f_goal($conn, $_POST['f_goal_id'], $_POST['user_id']);
                    break;
                }
            case "delete_scheduled_expenses": {
                    delete_scheduled_expenses($conn, $_POST['id']);
                    break;
                }
            case "set_readed_alert_scheduled": {
                    set_readed_alert_scheduled($_POST['id']);
                    break;
                }
            case "get_data_expenses_summary": {
                    get_cat_expense($_POST['date']);
                    break;
                }
            case "delete_alert_user": {
                    delete_alert_user($_POST['id']);
                    break;
                }
            case "delete_photo_expense": {
                    delete_photo_expense($_POST['id']);
                    break;
                }
            case "delete_debt": {
                    delete_debt($conn, $_POST["id"]);
                    break;
                }
        }
    }
}

function get_cat_expense($date)
{
    $backgroundColor = [
        "rgba(255, 110, 199, 0.2)",
        "rgba(27, 3, 163, 0.2)",
        "rgba(57, 255, 20, 0.2)",
        "rgba(255, 255, 51, 0.2)",
        "rgba(155, 77, 255, 0.2)",
        "rgba(255, 94, 0, 0.2)",
        "rgba(255, 7, 58, 0.2)",
        "rgba(0, 255, 239, 0.2)",
        "rgba(199, 125, 255, 0.2)",
        "rgba(0, 255, 0, 0.2)",
        "rgba(54, 191, 196, 0.2)",
        "rgba(0, 249, 249, 0.2)",
        "rgba(245, 0, 143, 0.2)",
        "rgba(0, 255, 255, 0.2)",
        "rgba(223, 255, 0, 0.2)",
        "rgba(75, 0, 130, 0.2)",
        "rgba(255, 0, 127, 0.2)",
        "rgba(152, 255, 152, 0.2)",
        "rgba(255, 106, 0, 0.2)",
        "rgba(255, 0, 255, 0.2)",
        "rgba(125, 249, 255, 0.2)",
        "rgba(76, 255, 0, 0.2)",
        "rgba(255, 99, 71, 0.2)",
        "rgba(255, 218, 3, 0.2)",
        "rgba(142, 60, 255, 0.2)",
        "rgba(255, 102, 102, 0.2)",
        "rgba(50, 205, 50, 0.2)",
        "rgba(218, 112, 214, 0.2)",
        "rgba(135, 206, 235, 0.2)",
        "rgba(138, 43, 226, 0.2)",
        "rgba(255, 191, 0, 0.2)",
        "rgba(184, 115, 51, 0.2)",
        "rgba(70, 130, 180, 0.2)"
    ];
    $borderColor = [
        "#FF3385",
        "#140280",
        "#2D9D0F",
        "#CCCC00",
        "#7A36CC",
        "#E94D00",
        "#D0002E",
        "#00B8C5",
        "#E68F8B",
        "#00B300",
        "#00C7C7",
        "#9B66D9",
        "#C40073",
        "#00B8B8",
        "#B3CC00",
        "#3D0066",
        "#D6006A",
        "#7ACC7A",
        "#E65C00",
        "#D100D1",
        "#5BC8D6",
        "#3AB300",
        "#E5533E",
        "#E6B700",
        "#6F2BBF",
        "#E55C5C",
        "#28A828",
        "#C14DC1",
        "#6B98D4",
        "#6E1BBA",
        "#E6A900",
        "#8C5927",
        "#3A6E8C"
    ];

    include("config.php");
    $query = "SELECT * FROM transactions WHERE user_id = $user_id AND type = 0 AND date >= '$date'";
    $result = $conn->query($query);
    $categorys_id = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (isset($categorys_id[0])) {
                for ($i = 0; $i < count($categorys_id); $i++) { {
                        if ($categorys_id[$i] == $row['cat_id']) {
                            $value[$i] += $row['value'];
                            break;
                        } else if ($i == count($categorys_id) - 1) {
                            $value[$i + 1] = $row['value'];
                            $categorys_id[] = $row['cat_id'];
                            break;
                        }
                    }
                }
            } else {
                $categorys_id[0] = $row['cat_id'];
                $value[0] = $row['value'];
            }
        }
        $data["status"] = 1;
        for ($i = 0; $i < count($categorys_id); $i++) {
            $data["backgroundColor"][] = [
                $backgroundColor[$categorys_id[$i]],
            ];
            $data["borderColor"][] = [
                $borderColor[$categorys_id[$i]],
            ];
            $data["catNames"][] = [
                get_category_name($conn, $categorys_id[$i]),
            ];
            $data["values"][] = [
                $value[$i],
            ];
        }
    } else {
        $data["status"] = 0;
        $data["values"][] = [0];
        $data["backgroundColor"][] = [0];
        $data["borderColor"][] = [0];
        $data["catNames"][] = [0];
    }
    $dataJson = json_encode($data);
    echo $dataJson;
}

function set_notif($notif)
{
    setcookie("notifications", $notif, time() + 10 * 365 * 24 * 60 * 60, "/");
}

function accept_request($type, $obj_id, $id, $role_id)
{
    include("config.php");
    delete_share_request($conn, $id);
    switch ($type) {
        case 1: {
                $query = "INSERT INTO accounts_user(account_id, user_id, role_id) VALUES($obj_id, $user_id, $role_id)";
                break;
            }
        case 2: {
                $query = "INSERT INTO objective_user(objective_id, user_id, role_id) VALUES($obj_id, $user_id, $role_id)";
                break;
            }
        case 3: {
                $query = "INSERT INTO financial_goal_user(financial_goal_id, user_id, role_id) VALUES($obj_id, $user_id, $role_id)";
                break;
            }
    }
    $conn->query($query);
}

function test_obj($type, $user_sent)
{
    include("config.php");

    switch ($type) {
        case "1": {
                $query = "SELECT DISTINCT a.*, au.role_id
FROM account a
JOIN accounts_user au ON a.id = au.account_id
JOIN role r ON au.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE au.user_id = $user_id 
AND p.name = 'manage_account_shares'
AND NOT EXISTS (
    SELECT 1
    FROM accounts_user au2
    WHERE au2.user_id = $user_sent 
    AND au2.account_id = a.id
)
AND NOT EXISTS (
    SELECT 1
    FROM share_request sr
    WHERE sr.type = $type
    AND sr.user_sent = $user_sent
    AND sr.obj_id = a.id
)";;
                break;
            }
        case "2": {
                $query = "SELECT DISTINCT o.*, ou.role_id
FROM objective o
JOIN objective_user ou ON o.id = ou.objective_id
JOIN role r ON ou.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE ou.user_id = $user_id 
AND p.name = 'manage_objective_shares'
AND NOT EXISTS (
    SELECT 1
    FROM objective_user ou2
    WHERE ou2.user_id = $user_sent 
    AND ou2.objective_id = o.id
)
AND NOT EXISTS (
    SELECT 1
    FROM share_request sr
    WHERE sr.type = $type
    AND sr.user_sent = $user_sent
    AND sr.obj_id = o.id
)";
                break;
            }
        case "3": {
                $query = "SELECT DISTINCT f.*, fu.role_id
FROM financial_goal f
JOIN financial_goal_user fu ON f.id = fu.financial_goal_id
JOIN role r ON fu.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE fu.user_id = $user_id 
AND p.name = 'manage_financial_goal_shares'
AND NOT EXISTS (
    SELECT 1
    FROM financial_goal_user fu2
    WHERE fu2.user_id = $user_sent 
    AND fu2.financial_goal_id = f.id
)
AND NOT EXISTS (
    SELECT 1
    FROM share_request sr
    WHERE sr.type = $type
    AND sr.user_sent = $user_sent
    AND sr.obj_id = f.id
)";
                break;
            }
    }

    $result = $conn->query($query);

    $response = "";
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $response .= '<option value="' . $row['id'] . '">
            ' . $row["name"] . '
        </option>';
        }
    }

    echo $response;
}

function search_user($id)
{
    include("config.php");
    if ($id != "" && $user_id != $id) {
        require_once("language.php");
        $query = "SELECT u.* FROM users u WHERE id = $id";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $query = "SELECT * FROM friend WHERE user_1 = $user_id AND user_2 = $id OR user_1 = $id AND user_2 = $user_id";
                $resultFriend = $conn->query($query);
                if ($resultFriend->num_rows > 0) {
                    $test = $resultFriend->fetch_assoc();
                    if ($test['status'] == 1) {
                        echo $user_friend;
                        return;
                    }
                }
                if ($resultFriend->num_rows == 0) {
                    $icon = "people-add";
                    $type = 0;
                } else {
                    $icon = "people-check";
                    $type = 1;
                }
                $_COOKIE['lights'] == 1 ? $box = 'box-shadow' : $box = '';
                $response = '<div class="card ' . $box . ' mt-2 mb-2 user' . $row["id"] . '">
       <div class="row">
           <div class="col-sm-5 col-6">
               <h6>' . $row['name'] . '</h6>
           </div>
           <div class="col-sm-5 col-4"></div>
           <div class="col-2"> <a href="#" id="icon-a" onclick="sendRequest(' . $id . ', ' . $type . ')"><svg class="bi bi-2">
                       <use id="icon-result" xlink:href="#' . $icon . '" />
                   </svg></a></div>
       </div>
   </div>';

                echo $response;
            }
        } else
            echo $not_found;
    }
}

function set_mode($mode)
{
    $expiration = time() + 60 * 60 * 24 * 365 * 10;
    setcookie("mode", $mode, $expiration, "/");
}

function set_lights($light)
{
    $expiration = time() + 60 * 60 * 24 * 365 * 10;
    setcookie("lights", $light, $expiration, "/");
}

function set_coin($conn, $id)
{
    $query = "SELECT * FROM coin WHERE id = $id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $expiration = time() + 60 * 60 * 24 * 365 * 10;
    setcookie("coin_id", $id, $expiration, "/");
    setcookie("coin_symbol", $row['symbol'], $expiration, "/");
}


if (isset($_SESSION['page'])) {
    switch ($_SESSION['page']) {
        case "home": {
                // Saldo
                $cash = get_cash($conn, $user_id);

                $query = "SELECT DISTINCT t.*, au.role_id
                        FROM transactions t
JOIN accounts_user au ON t.account_id = au.account_id
JOIN role r ON au.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE au.user_id = $user_id
AND p.name = 'edit_user_cash'
 ORDER BY date ASC, type DESC, id DESC;";
                $result_transactions_order = $conn->query($query);

                $result_cats = get_all_category($conn);
                $i = 0;
                $cats = [];
                while ($row = mysqli_fetch_assoc($result_cats)) {
                    if ($_COOKIE['lang'] == "EN")
                        $cats[$i] = $row['name'];
                    else
                        $cats[$i] = $row['name_pt'];
                    $i++;
                }

                $total_transactions_table = [];
                $days = [0, 0, 0, 0, 0, 0, 0];
                $i = 0;
                $numTransactions = $result_transactions_order->num_rows;
                $last_date = 0;
                $j = 0;
                $total_transactions_table[-1] = 0;
                $totalExpense = 0;
                $totalRevenues = 0;
                while ($row = mysqli_fetch_assoc($result_transactions_order)) {
                    if ($row['type'] == 0)
                        $totalExpense += $row['value'];
                    else
                        $totalRevenues += $row['value'];

                    if ($row['date'] != $last_date)
                        $j++;
                    if ($row['type'] == 1)
                        $total_transactions_table[$i] = $total_transactions_table[$i - 1] + $row['value'];
                    else
                        $total_transactions_table[$i] = $total_transactions_table[$i - 1] - $row['value'];
                    $last_date = $row['date'];
                    $numTransactions--;
                    $i++;
                }

                $last_date = 0;
                $revenues = 0;
                $expenses = 0;
                $days[-1] = 0;
                for ($i = 0; $i <= 365; $i++) {
                    $days[$i] = $cash;
                    $date = new DateTime('now');
                    $date = $date->modify("-$i Day");
                    $date = $date->format("Y-m-d");
                    $result_days = get_transactions($conn, $user_id, 0, 0, 0, 1, $date);
                    $days[$i] += $expenses;
                    $days[$i] -= $revenues;

                    while ($row = mysqli_fetch_assoc($result_days)) {
                        if ($row['type'] == 0)
                            $expenses += $row['value'];
                        else
                            $revenues += $row['value'];
                    }
                    $last_date = $date;
                }

                $days_name = [];

                for ($i = 0; $i < 366; $i++) {
                    $days_name[$i] = new DateTime('now');
                    $days_name[$i] = $days_name[$i]->modify("-$i Day");
                    $days_name[$i] = $days_name[$i]->format("d/m");
                }

                $this_year = new Datetime("now");
                $this_year = $this_year->format("Y-01-01");
                $resultThisYear =  get_transactions($conn, $user_id, 0, 0, 0, 1, 0, 1, $this_year);
                $cashThisYear = 0;
                while ($row = $resultThisYear->fetch_assoc()) {
                    $cashThisYear += $row['value'];
                }

                break;
            }
        case "manage transactions": {
                $total_transactions_table = [];
                $total_transactions_table[-1] = 0;
                $query = "SELECT DISTINCT t.*, au.role_id
                        FROM transactions t
JOIN accounts_user au ON t.account_id = au.account_id
JOIN role r ON au.role_id = r.id
JOIN role_permission rp ON r.id = rp.role_id
JOIN permission p ON rp.permission_id = p.id
WHERE au.user_id = $user_id
AND p.name = 'view_transactions'
ORDER BY date ASC, type DESC, id DESC";
                $result = $conn->query($query);
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    if (hasPermission($conn, $row['role_id'], "edit_user_cash")) {
                        if ($row['type'] == 1)
                            $total_transactions_table[$i] = $total_transactions_table[$i - 1] + $row['value'];
                        else
                            $total_transactions_table[$i] = $total_transactions_table[$i - 1] - $row['value'];
                    }
                    $i++;
                }

                break;
            }
        case "monthly comparison": {
                $query = "SELECT DISTINCT t.*, au.role_id
            FROM transactions t
            JOIN accounts_user au ON t.account_id = au.account_id
            JOIN role r ON au.role_id = r.id
            JOIN role_permission rp ON r.id = rp.role_id
            JOIN permission p ON rp.permission_id = p.id
            WHERE au.user_id = $user_id
             AND p.name = 'edit_user_cash' ORDER BY date DESC";
                $result = $conn->query($query);
                $last_date = 0;
                $i = -1;
                while ($row = $result->fetch_assoc()) {
                    $curDate = new Datetime($row['date']);
                    $curDate = $curDate->format("y-m");

                    if ($i == -1) {

                        $last_date = $curDate;
                        $i++;
                        $month_name[$i] = $curDate;
                    }
                    if ($last_date != $curDate) {
                        $i++;
                        $last_date = $curDate;
                        $month_name[$i] = $curDate;
                    }

                    if ($row['type'] == 0) {
                        if (!isset($month[$i]))
                            $month[$i] = -$row['value'];
                        else
                            $month[$i] -= $row['value'];
                        if (!isset($expenses[$i]))
                            $expenses[$i] = $row['value'];
                        else
                            $expenses[$i] += $row['value'];
                    } else {
                        if (!isset($month[$i]))
                            $month[$i] = $row['value'];
                        else
                            $month[$i] += $row['value'];
                        if (!isset($revenues[$i]))
                            $revenues[$i] = $row['value'];
                        else
                            $revenues[$i] += $row['value'];
                    }
                }
                $maxMonths = $i;

                break;
            }
        case "monthly summary": {
                $result = get_transactions($conn, $user_id, 0, 0, 0, 1);
                $i = -1;
                while ($row = $result->fetch_assoc()) {
                    $curDate = new Datetime($row['date']);
                    $curDate = $curDate->format("Y-M");
                    if ($i == -1) {
                        $last_date = $curDate;
                        $i++;
                        $month_name[$i] = $curDate;
                    }
                    if ($last_date != $curDate) {
                        $last_date = $curDate;
                        $i++;
                        $month_name[$i] = $curDate;
                    }

                    if ($row['type'] == 0) {
                        if (!isset($expenses[$i]))
                            $expenses[$i] = $row['value'];
                        else
                            $expenses[$i] += $row['value'];
                    } else {
                        if (!isset($revenues[$i]))
                            $revenues[$i] = $row['value'];
                        else
                            $revenues[$i] += $row['value'];
                    }
                }

                $maxMonths = $i;
                break;
            }
        case "year comparison": {
                $result = get_transactions($conn, $user_id, 0, 0, 0, 1);

                $last_date = 0;
                $i = -1;
                while ($row = $result->fetch_assoc()) {
                    $curDate = new Datetime($row['date']);
                    $curDate = $curDate->format("Y");

                    if ($i == -1) {
                        $last_date = $curDate;
                        $i++;
                        $month_name[$i] = $curDate;
                    }
                    if ($last_date != $curDate) {
                        $i++;
                        $last_date = $curDate;
                        $month_name[$i] = $curDate;
                    }

                    if ($row['type'] == 0) {
                        if (!isset($month[$i]))
                            $month[$i] = -$row['value'];
                        else
                            $month[$i] -= $row['value'];
                        if (!isset($expenses[$i]))
                            $expenses[$i] = $row['value'];
                        else
                            $expenses[$i] += $row['value'];
                    } else {
                        if (!isset($month[$i]))
                            $month[$i] = $row['value'];
                        else
                            $month[$i] += $row['value'];
                        if (!isset($revenues[$i]))
                            $revenues[$i] = $row['value'];
                        else
                            $revenues[$i] += $row['value'];
                    }
                }
                $maxMonths = $i;

                break;
            }
    }
}