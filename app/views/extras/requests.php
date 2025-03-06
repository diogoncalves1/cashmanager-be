<link rel="stylesheet" href="/CashManager/public/assets/css/form.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "requests";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $share_requests; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">
                    <?Php echo $add_expense; ?>
                </button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-10 col-11 text-center">
                <div class="row cards">
                    <?php $result = get_share_request($conn, $user_id);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                    ?>
                            <div
                                class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 request<?= $row['id']; ?>">
                                <div class="row">
                                    <?php
                                    $userResult = get_user($conn, $row["user_sending"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                                    ?>
                                        <div class="col-sm-3 col-3">
                                            <h6><?= $userSend["name"]; ?> ID:<?= $userSend["id"]; ?></h6>
                                        </div>
                                        <div class="col-sm-4 col-4">
                                            <h6><?= $words["position"];  ?>: <?= $words[get_role_name($conn, $row["role_id"])] ?>
                                            </h6>
                                        </div>
                                        <div class="col-sm-3 col-3">
                                            <h6><?= $words[get_share_type_name($conn, $row["type"])];  ?>: <?php
                                                                                                            switch ($row['type']) {
                                                                                                                case 1: {
                                                                                                                        echo get_name_account($conn, $row['obj_id']);
                                                                                                                        break;
                                                                                                                    }
                                                                                                                case 2: {
                                                                                                                        echo get_objective_name_by_id($conn, $row['obj_id']);
                                                                                                                        break;
                                                                                                                    }
                                                                                                                case 3: {
                                                                                                                        echo get_name_financial_goal($conn, $row['obj_id']);
                                                                                                                        break;
                                                                                                                    }
                                                                                                            } ?>
                                            </h6>
                                        </div>

                                        <div class="col-1"> <a id="icon-a" href="#"
                                                onclick="confirm(<?= $row['type']; ?>, <?= $row['obj_id']; ?>, <?= $row['id']; ?>, <?= $row['role_id'] ?>)"><svg
                                                    class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#check" />
                                                </svg></a></div>
                                        <div class="col-1"> <a id="icon-a" href="#" onclick="reject(<?= $row['id']; ?>)"><svg
                                                    class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#x" />
                                                </svg></a></div>
                                </div>
                            </div>
                <?php   }
                                }
                            } ?>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/js/requests.js"></script>