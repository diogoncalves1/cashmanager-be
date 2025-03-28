<link rel="stylesheet" href="/CashManager/public/assets/css/objectives.css">
<?php $this->layout("master"); ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["completed_objectives"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"] ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"] ?></button>
            </div>
        </div>
    </div>
    <?php foreach ($userCompletedObjectives as $objective) { ?>
    <div class="page-content page-container mb-2" id="page-content">
        <div class="row container d-flex justify-content-center">
            <div class="col-xl-12">
                <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> proj-progress-card">
                    <div class="card-block">
                        <div class="row pb-1">
                            <div class="col">
                                <h5><?= $translate["objective"]; ?>: <?php echo $objective['name']; ?></h5>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-6 col-lg-3">
                                <h6><?= $translate["missing_amount"]; ?></h6>
                                <h5 class="m-b-30 f-w-700"><span
                                        class="text-c-red m-l-10"><?php echo $objective['objective_value'] - $objective['amount_invested']; ?><?= $userCoin; ?></span>
                                </h5>
                            </div>
                            <div class="col-6 col-lg-3">
                                <h6><?= $translate["amount_invested"]; ?></h6>
                                <h5 class="m-b-30 f-w-700"><span
                                        class="text-c-green m-l-10"><?php echo $objective['amount_invested']; ?><?= $userCoin; ?></span>
                                </h5>
                            </div>
                            <div class="col-6 col-lg-3">
                                <h6><?= $translate["progress"]; ?></h6>
                                <h5 class="m-b-30 f-w-700"><span
                                        class="text-c-green m-l-10"><?php echo porcent($objective['amount_invested'], $objective['objective_value']); ?>%</span>
                                </h5>
                            </div>
                            <div class="col-6 col-lg-3">
                                <h6><?= $translate["objective_value"]; ?></h6>
                                <h5 class="m-b-30 f-w-700"><span
                                        class="text-c-blue m-l-10"><?php echo $objective['objective_value']; ?><?= $userCoin; ?></span>
                                </h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                        role="progressbar"
                                        style="width: <?= porcent($objective['amount_invested'], $objective['objective_value']); ?>%">
                                        <?= porcent($objective['amount_invested'], $objective['objective_value']); ?>%
                                    </div>
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