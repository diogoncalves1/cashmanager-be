<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "investing";
require_once "../backend/querys.php";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}

require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <?Php echo $investing_in_the_objective; ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">
                    <?Php echo $add_expense; ?>
                </button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">
                    <?Php echo $add_revenue; ?>
                </button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" name="form" action="" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $value; ?> Max: <span
                                    id="max"><?= $max > $maxGoal ? $maxGoal : $max; ?></span><?= $coin ?></label> <input
                                type="number" min="0" max="<?php echo $max; ?>" class="input" step="0.01"
                                id="value-input" name="value" placeholder="0.00<?= $coin ?>" required> </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $account; ?></label>
                            <select name="account" id="account-select" onchange="updateMax()">
                                <?php foreach ($accountsUser as $account) { ?>
                                    <option role="<?= $account['cash']; ?>" id="<?= $account['id']; ?>"
                                        value="<?= $account['id']; ?>"><?= $account['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $objective; ?> - <?= $total_remaining; ?>:
                                <span id="total-remaining"><?= $maxGoal; ?></span><?= $coin ?></label>
                            <select name="objective" id="objetive-select" onchange="updateMax()">
                                <?php foreach ($objectivesUser as $objective) {
                                    if ($objective['meta'] != $objective['now']) { ?>
                                        <option role="<?= $objective['meta'] - $objective['now']; ?>" id="<?= $objective['id']; ?>"
                                            value="<?php echo $objective['id']; ?>"><?php echo $objective['name']; ?></option>
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