<?php $this->layout("master"); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h1 class="h2 mt-3"><?= $translate["welcome"]; ?> <?= $userName; ?></h1>

    <div class="row pt-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom cur-pointer border-left-success <?= $lightTest == 1 ? "box-shadow-success" : "" ?> h-100 py-2"
                onclick="goToManageTransactions(1)">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= $translate["earnings"]; ?> (<?= $translate["ever"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $totalRevenues; ?><?= $userCoin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $userCoin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card cur-pointer zoom border-left-danger <?= $lightTest == 1 ? "box-shadow-danger" : "" ?> h-100 py-2"
                onclick="goToManageTransactions(0)">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?= $translate["expenses"]; ?> (<?= $translate["ever"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $totalExpenses; ?><?= $userCoin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $userCoin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom border-left-primary <?= $lightTest == 1 ? "box-shadow-primary" : "" ?> h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= $translate["total"]; ?> (<?= $translate["current"]; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $userCash; ?><?= $userCoin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $userCoin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom border-left-violet <?= $lightTest == 1 ? "box-shadow-violet" : "" ?> h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-violet text-uppercase mb-1">
                                <?= $translate["earnings"]; ?> (<?= $translate["this"] . " " . $translate["year"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $revenuesThisYear; ?><?= $userCoin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $userCoin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($numMaxUserTransactions) { ?>
    <h2><?php echo $numMaxUserTransactions . " " . $translate["last_transactions"]; ?></h2>
    <div class="table-responsive small">
        <table class="table table-striped table-sm table-hover cur-pointer">
            <thead>
                <tr>
                    <th scope="col"><?= $translate["date"]; ?></th>
                    <th scope="col"><?= $translate["type"]; ?></th>
                    <th scope="col"><?= $translate["account"]; ?></th>
                    <th scope="col"><?= $translate["value"]; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($userTransactions as $transaction) {
                    ?>
                <tr onclick="goToviewTransaction(<?= $transaction['id']; ?>)"
                    class="<?php echo $transaction["class"] . " " . $transaction["light"] ?>">
                    <td style="color: <?= $transaction["color"]; ?>"><?= $transaction['date']; ?></td>
                    <td style="color: <?= $transaction["color"]; ?>"><?= $translate[$transaction['type']]; ?></td>
                    <td style="color: <?= $transaction["color"]; ?>"> <?= $transaction["account"]; ?></td>
                    <td style="color: <?= $transaction["color"]; ?>"><?= $transaction['value'] . $userCoin; ?></td>
                </tr>
                <?php
                    } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</main>

<?php
include("../assets/js/charts/week-bar.php");
include("../assets/js/charts/month-bar.php");
include("../assets/js/charts/year-bar.php");
?>