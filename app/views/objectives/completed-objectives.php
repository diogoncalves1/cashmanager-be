<link rel="stylesheet" href="/CashManager/public/assets/css/objectives.css">
<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "claimed goals";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["completed_objectives"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">Add
                    Expense</button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">Add
                    Revenue</button>
            </div>
        </div>
    </div>
    <?php $resultObjectives = get_objectives($conn, $user_id, 1, 0, 0, 1);
    while ($row = $resultObjectives->fetch_assoc()) { ?>
        <div class="page-content page-container mb-2" id="page-content">
            <div class="row container d-flex justify-content-center">
                <div class="col-xl-12">
                    <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> proj-progress-card">
                        <div class="card-block">
                            <div class="row pb-1">
                                <div class="col">
                                    <h5><?php echo $objective; ?>: <?php echo $row['name']; ?></h5>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-6 col-lg-3">
                                    <h6><?php echo $missing_money; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-red m-l-10"><?php echo $row['meta'] - $row['now']; ?><?= $coin; ?></span>
                                    </h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?php echo $money_invested; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-green m-l-10"><?php echo $row['now']; ?><?= $coin; ?></span></h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?php echo $progress; ?></h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-green m-l-10"><?php echo porcent($row['now'], $row['meta']); ?>%</span>
                                    </h5>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h6><?php echo $objective; ?> Total</h6>
                                    <h5 class="m-b-30 f-w-700"><span
                                            class="text-c-blue m-l-10"><?php echo $row['meta']; ?><?= $coin; ?></span></h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                            role="progressbar" style="width: <?= porcent($row['now'], $row['meta']); ?>%">
                                            <?= porcent($row['now'], $row['meta']); ?>%</div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</main>