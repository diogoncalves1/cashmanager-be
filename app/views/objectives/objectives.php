<link rel="stylesheet" href="/CashManager/public/assets/css/objectives.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "goals";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    reedem_objective($conn, $_POST['objective']);
}
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <?php if (isset($_SESSION["alert"])) { ?>
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
            <div id="checkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                    role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    <div><?= $_SESSION['alert'] ?></div><button type="button" class="btn-close me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php
        unset($_SESSION["alert"]);
    }  ?>

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $goals; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">Add
                    Expense</button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">Add
                    Revenue</button>
            </div>
        </div>
    </div>

    <?php $resultObjectives = get_objectives($conn, $user_id, 1);
    while ($row = $resultObjectives->fetch_assoc()) { ?>
        <div class="page-content page-container mb-2" id="page-content">
            <div class="row container d-flex justify-content-center">
                <div class="col-xl-12">
                    <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> proj-progress-card">
                        <div class="card-block">
                            <div class="row pb-1">
                                <div class="col">
                                    <h5><?= $objective; ?>: <?= $row['name']; ?></h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-lg-3">
                                    <h6><?= $missing_money; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-red m-l-10"><?= $row['meta'] - $row['now']; ?><?= $coin ?></span>
                                    </h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?= $money_invested; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-green m-l-10"><?= $row['now']; ?><?= $coin ?></span>
                                    </h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?= $progress; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-green m-l-10"><?= porcent($row['now'], $row['meta']); ?>%</span>
                                    </h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?= $objective; ?> Total</h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-blue m-l-10"><?= $row['meta']; ?><?= $coin ?></span>
                                    </h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: <?= porcent($row['now'], $row['meta']); ?>%">
                                            <?= porcent($row['now'], $row['meta']); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($row['meta'] == $row['now'] && $row['claimed'] == 0 && hasPermission($conn, $row["role_id"], "claim_objective")) { ?>
                                <div class="row">
                                    <div class="col">
                                        <form method="POST">
                                            <button type="submit" name="objective" class="btn btn-success"
                                                value="<?= $row['id']; ?>"><?= $redeem; ?></button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</main>
<script src="../../assets/js/view-objective.js"></script>