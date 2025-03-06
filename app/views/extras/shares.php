<link rel="stylesheet" href="/CashManager/public/assets/css/form.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "shares";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $shares; ?></h1>
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
        <div class="row d-flex justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-10 col-11 text-center">

                <h3><?= $shared_accounts; ?></h3>
                <div class="row cards">
                    <?php $result = get_shares($conn, $user_id, 1);
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                    ?>
                            <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 share-acc-<?= $i ?>">
                                <div class="row">
                                    <?php
                                    $userResult = get_user($conn, $row["user_id"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                                    ?>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= $userSend["name"]; ?> ID:<?= $userSend["id"]; ?></h6>
                                        </div>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= get_name_account($conn, $row['id']); ?></h6>
                                        </div>

                                        <div class="col-1">
                                            <a href="#" id="icon-a" onclick="goToEditRole(<?= $row['id']; ?>)">
                                                <svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#edit" />
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="col-1">
                                            <a href="#" id="icon-a" data-bs-toggle="modal"
                                                data-bs-target="#delete-modal-account"
                                                onclick="modalAccount(<?= $row['id']; ?>, <?= $i ?>, <?= $row['user_id'] ?>)">
                                                <svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#delete" />
                                                </svg>
                                            </a>
                                        </div>
                                </div>
                            </div>
                    <?php  }
                                    $i++;
                                }
                            } else { ?>
                    <p class="text-center"><?= $no; ?> <?= $shared_accounts; ?></p>
                <?Php } ?>
                </div>

                <h3><?= $shared_objectives; ?></h3>
                <div class="row cards">
                    <?php $result = get_shares($conn, $user_id, 2);
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                    ?>
                            <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 share-obj-<?= $i; ?>">
                                <div class="row">
                                    <?php
                                    $userResult = get_user($conn, $row["user_id"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                                    ?>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= $userSend["name"]; ?> ID:<?= $userSend["id"]; ?></h6>
                                        </div>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= get_objective_name_by_id($conn, $row['id']); ?></h6>
                                        </div>
                                        <div class="col-1">
                                            <a href="#" id="icon-a" onclick="goToEditRole(<?= $row['id']; ?>)">
                                                <svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#edit" />
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="col-1"> <a href="#" id="icon-a" data-bs-toggle="modal"
                                                data-bs-target="#delete-modal-objective"
                                                onclick="modalObjective(<?= $row['id'] ?>,<?= $i ?>, <?= $row['user_id'] ?>)"><svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#delete" />
                                                </svg></a></div>
                                </div>
                            </div>
                    <?php    }
                                    $i++;
                                }
                            } else { ?>
                    <p class="text-center"><?= $no; ?> <?= $shared_objectives; ?></p>
                <?Php } ?>
                </div>

                <h3><?= $shared_financial_goals; ?></h3>
                <div class="row cards">
                    <?php $result = get_shares($conn, $user_id, 3);
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {   ?>
                            <div
                                class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 share-f-goal-<?= $i; ?>">
                                <div class="row">
                                    <?php
                                    $userResult = get_user($conn, $row["user_id"]);
                                    while ($userSend = $userResult->fetch_assoc()) {
                                    ?>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= $userSend["name"]; ?> ID:<?= $userSend["id"]; ?></h6>
                                        </div>
                                        <div class="col-sm-5 col-5">
                                            <h6><?= get_name_financial_goal($conn, $row['id']); ?></h6>
                                        </div>
                                        <div class="col-1">
                                            <a href="#" id="icon-a" onclick="goToEditRole(<?= $row['id']; ?>)">
                                                <svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#edit" />
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="col-1">
                                            <a href="#" id="icon-a" data-bs-toggle="modal" data-bs-target="#delete-modal-financial-goal"
                                                onclick="modalFinancialGoal(<?= $row['id'] ?>, <?= $i ?>, <?= $row['user_id'] ?>)">
                                                <svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#delete" />
                                                </svg>
                                            </a>
                                        </div>
                                </div>
                            </div>
                    <?php
                                    }
                                    $i++;
                                }
                            } else { ?>
                    <p class="text-center"><?= $no; ?> <?= $shared_accounts; ?></p>
                <?Php }  ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-modal-account" tabindex="-1" aria-labelledby="delete-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-4"><?= $modal_account_shared; ?>?</h5>
                    <button type="button" id="modal-btn-account" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-modal-objective" tabindex="-1" aria-labelledby="delete-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-4"><?= $modal_objetive_shared; ?>?</h5>
                    <button type="button" id="modal-btn-objective" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete-modal-financial-goal" tabindex="-1"
        aria-labelledby="delete-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-4"><?= $modal_financial_goal_shared; ?>?</h5>
                    <button type="button" id="modal-btn-financial-goals" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>

</main>
<script src="/CashManager/public/assets/js/shares.js"></script>