<link rel="stylesheet" href="../../assets/css/form.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "add friend";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $add_friend; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?Php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <h5><?= $your ?> ID: <?= $user_id ?></h5>
        <div class="row d-flex justify-content-center">
            <div class="col-xl-10 col-lg-10 col-md-10 col-11 text-center">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <div class="row justify-content-between text-left">
                            <div class="form-group col-12 flex-column d-flex"> <label
                                    class="form-control-label px-3">ID<span class="text-danger-emphasis">
                                        *</span></label> <input type="number" class="input"
                                    id="search-input" name="value" min="1" placeholder="Ex: 123456"
                                    required>
                            </div>
                        </div>

                        <div id="search-result">
                        </div>

                        <div class="row justify-content-end">
                            <div class="form-group col-sm-6"> <button type="button"
                                    class="button btn btn-sm btn-outline-success"
                                    id="btn-search"><?php echo $search ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <h5>Pedidos Eviados</h5>
                        <div id="request-sended">
                            <?php $result = get_friend_request($conn, $user_id, 1, 0);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $userResult = get_user($conn, $row["user_2"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                            ?>
                                        <div
                                            class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 user<?= $row['user_2']; ?>">
                                            <div class="row">
                                                <div class="col-sm-5 col-6">
                                                    <h6><?= $userSend["name"]; ?></h6>
                                                </div>
                                                <div class="col-sm-5 col-4"></div>
                                                <div class="col-2"> <a href="#" id="icon-a"
                                                        onclick="sendRequest('<?= $userSend['id'] ?>', 1)"><svg
                                                            class="bi bi-2">
                                                            <use id="icon-result" xlink:href="#people-check" />
                                                        </svg></a></div>
                                            </div>
                                        </div>
                                <?php }
                                }
                            } else { ?>
                                <p><?= $sended_request; ?></p>
                            <?php } ?>
                        </div>

                        <hr class="my-3">
                        <h5>Pedidos Recebidos</h5>
                        <div id="received-requests">
                            <?php $result = get_friend_request($conn, $user_id, 0, 0);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $userResult = get_user($conn, $row["user_1"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                            ?>
                                        <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 "
                                            id="received<?= $userSend['id']; ?>">
                                            <div class="row">
                                                <div class="col-sm-5 col-6">
                                                    <h6><?= $userSend["name"]; ?></h6>
                                                </div>
                                                <div class="col-sm-3 col-2"></div>
                                                <div class="col-2"> <a id="icon-a" href="#"
                                                        onclick="confirm(<?= $userSend['id'] ?>, 1)"><svg
                                                            class="bi bi-2">
                                                            <use id="icon-result" xlink:href="#check" />
                                                        </svg></a></div>
                                                <div class="col-2"> <a id="icon-a" href="#"
                                                        onclick="confirm(<?= $userSend['id'] ?>, 0)"><svg
                                                            class="bi bi-2">
                                                            <use id="icon-result" xlink:href="#x" />
                                                        </svg></a></div>
                                            </div>
                                        </div>
                                <?php }
                                }
                            } else { ?>
                                <p><?= $received_request ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../../assets/js/add-friendd.js"></script>