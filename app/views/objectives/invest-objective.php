<?php $this->layout("master"); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <?= $translate["investing_in_the_objective"]; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">
                    <?= $translate["add_expense"]; ?>
                </button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">
                    <?= $translate["add_revenue"]; ?>
                </button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" name="form" action="" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $translate["value"]; ?> - Max:
                                <span id="max"><?= $maxGoal; ?></span><?= $userCoin ?>
                            </label>
                            <input type="number" min="0" max="<?= $maxGoal; ?>" class="input" step="0.01"
                                id="value-input" name="value" placeholder="0.00<?= $userCoin ?>" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $translate["account"]; ?></label>
                            <select name="account" id="account-select" onchange="updateMax()">
                                <?php foreach ($accountsUser as $account) { ?>
                                <option role="<?= $account['cash']; ?>" id="<?= $account['id']; ?>"
                                    value="<?= $account['id']; ?>"><?= $account['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $translate["objective"]; ?> -
                                <?= $translate["missing_amount"]; ?>:
                                <span id="total-remaining"><?= $maxGoal; ?></span><?= $userCoin ?></label>
                            <select name="objective" id="objetive-select" onchange="updateMax()">
                                <?php foreach ($objectivesUser as $objective) {
                                    if ($objective['objective_value'] != $objective['amount_invested']) { ?>
                                <option role="<?= $objective['objective_value'] - $objective['amount_invested']; ?>"
                                    id="<?= $objective['id']; ?>" value="<?php echo $objective['id']; ?>">
                                    <?= $objective['name']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6">
                            <button type="submit" class="button btn btn-sm btn-outline-success">Invest</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script src="../../assets/js/objective.js"></script>